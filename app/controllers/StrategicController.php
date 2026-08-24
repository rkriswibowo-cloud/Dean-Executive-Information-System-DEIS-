<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Indicator;
use App\Models\IndicatorTarget;
use App\Models\IndicatorRealization;
use App\Models\RenstraProgram;
use App\Services\AuditService;
use App\Helpers\AuthHelper;

class StrategicController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $indicatorModel = new Indicator();
        $renstraModel = new RenstraProgram();

        $indicators = $indicatorModel->allWithTargetAndRealization(2026);
        $renstraPrograms = $renstraModel->all();

        // Calculate average achievement
        $achievements = array_filter(array_column($indicators, 'achievement_percentage'), fn($v) => $v !== null);
        $avgAchievement = count($achievements) > 0 ? round(array_sum($achievements) / count($achievements), 1) : 0;

        $this->render('strategic/index', [
            'title'           => 'Kinerja Strategis & Capaian IKU',
            'indicators'      => $indicators,
            'renstraPrograms' => $renstraPrograms,
            'avgAchievement'  => $avgAchievement
        ]);
    }

    public function indicators(): void {
        $this->requireAuth();

        $indicatorModel = new Indicator();
        $indicators = $indicatorModel->all('id ASC');

        $this->render('strategic/indicators', [
            'title'      => 'Master Indikator Kinerja Strategis (Dinamis)',
            'indicators' => $indicators
        ]);
    }

    public function saveRealization(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $targetId = (int)$this->getPost('target_id', 0);
        $realizationValue = (float)$this->getPost('realization_value', 0);
        $targetValue = (float)$this->getPost('target_value', 1);
        $notes = trim($this->getPost('notes', ''));

        if ($targetValue <= 0) $targetValue = 1;
        $achievement = round(($realizationValue / $targetValue) * 100, 2);

        $status = 'Success';
        if ($achievement < 70) {
            $status = 'Critical';
        } elseif ($achievement < 85) {
            $status = 'Warning';
        } elseif ($achievement < 100) {
            $status = 'Attention';
        }

        $realizationModel = new IndicatorRealization();
        $existing = $realizationModel->whereOne('indicator_target_id', $targetId);

        $user = AuthHelper::user();

        if ($existing) {
            $realizationModel->update($existing['id'], [
                'realization_value'      => $realizationValue,
                'achievement_percentage' => $achievement,
                'status'                 => $status,
                'notes'                  => $notes,
                'verified_by'            => $user['name'],
                'verified_at'            => date('Y-m-d H:i:s')
            ]);
            AuditService::log('UPDATE', 'indicator_realizations', (string)$existing['id'], $existing, ['realization_value' => $realizationValue]);
        } else {
            $realizationModel->create([
                'indicator_target_id'    => $targetId,
                'realization_value'      => $realizationValue,
                'achievement_percentage' => $achievement,
                'status'                 => $status,
                'notes'                  => $notes,
                'verified_by'            => $user['name'],
                'verified_at'            => date('Y-m-d H:i:s')
            ]);
            AuditService::log('CREATE', 'indicator_realizations', (string)$targetId, null, ['realization_value' => $realizationValue]);
        }

        $this->redirect('strategic', ['success' => 'Realisasi indikator berhasil disimpan & dihitung.']);
    }
}
