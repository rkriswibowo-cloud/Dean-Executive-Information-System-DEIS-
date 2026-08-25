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

        // Calculate average achievement (capped at 100% per indicator)
        $achievements = array_map(function($ind) {
            return min(100.0, (float)($ind['achievement_percentage'] ?? 0));
        }, $indicators);
        $avgAchievement = count($achievements) > 0 ? round(array_sum($achievements) / count($achievements), 1) : 0;
        $avgAchievement = min(100.0, $avgAchievement);

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
        $indicators = $indicatorModel->allWithTargetAndRealization(2026, false);

        $this->render('strategic/indicators', [
            'title'      => 'Master Indikator Kinerja Strategis (IKU & Renstra)',
            'indicators' => $indicators
        ]);
    }

    public function createIndicator(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $code = strtoupper(trim($this->getPost('code', '')));
        $name = trim($this->getPost('name', ''));
        $category = trim($this->getPost('category', 'IKU'));
        $formula = trim($this->getPost('formula', ''));
        $unit = trim($this->getPost('unit', '%'));
        $dataSource = trim($this->getPost('data_source', ''));
        $targetValue = (float)$this->getPost('target_value', 100);
        $isActive = (int)$this->getPost('is_active', 1);

        if (empty($code) || empty($name)) {
            $this->redirect('strategic/indicators', ['error' => 'Kode dan Nama Indikator wajib diisi.']);
            return;
        }

        $indicatorModel = new Indicator();
        if ($indicatorModel->findByCode($code)) {
            $this->redirect('strategic/indicators', ['error' => "Kode Indikator '{$code}' sudah digunakan."]);
            return;
        }

        $newIndicatorId = $indicatorModel->create([
            'code'        => $code,
            'name'        => $name,
            'category'    => in_array($category, ['IKU', 'Renstra', 'Fakultas', 'SPMI']) ? $category : 'IKU',
            'formula'     => $formula,
            'unit'        => $unit ?: '%',
            'data_source' => $dataSource,
            'is_active'   => $isActive
        ]);

        // Create default target for year 2026
        $targetModel = new IndicatorTarget();
        $targetId = $targetModel->create([
            'indicator_id' => $newIndicatorId,
            'faculty_id'   => 1,
            'year'         => 2026,
            'period'       => 'Tahunan',
            'target_value' => $targetValue
        ]);

        // Create default realization row
        $realizationModel = new IndicatorRealization();
        $realizationModel->create([
            'indicator_target_id'    => $targetId,
            'realization_value'      => 0,
            'achievement_percentage' => 0,
            'status'                 => 'Attention',
            'notes'                  => 'Inisialisasi target indikator baru.',
            'verified_by'            => AuthHelper::user()['name'] ?? 'Admin',
            'verified_at'            => date('Y-m-d H:i:s')
        ]);

        AuditService::log('CREATE', 'indicators', (string)$newIndicatorId, null, [
            'code'         => $code,
            'name'         => $name,
            'target_value' => $targetValue
        ]);

        $this->redirect('strategic/indicators', ['success' => "Indikator {$code} - {$name} berhasil ditambahkan beserta target 2026."]);
    }

    public function updateIndicator(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        $code = strtoupper(trim($this->getPost('code', '')));
        $name = trim($this->getPost('name', ''));
        $category = trim($this->getPost('category', 'IKU'));
        $formula = trim($this->getPost('formula', ''));
        $unit = trim($this->getPost('unit', '%'));
        $dataSource = trim($this->getPost('data_source', ''));
        $targetValue = (float)$this->getPost('target_value', 100);
        $isActive = (int)$this->getPost('is_active', 1);

        if ($id <= 0 || empty($code) || empty($name)) {
            $this->redirect('strategic/indicators', ['error' => 'Data indikator tidak valid atau belum lengkap.']);
            return;
        }

        $indicatorModel = new Indicator();
        $existing = $indicatorModel->find($id);
        if (!$existing) {
            $this->redirect('strategic/indicators', ['error' => 'Indikator tidak ditemukan.']);
            return;
        }

        if ($indicatorModel->findByCode($code, $id)) {
            $this->redirect('strategic/indicators', ['error' => "Kode Indikator '{$code}' sudah digunakan oleh indikator lain."]);
            return;
        }

        $indicatorModel->update($id, [
            'code'        => $code,
            'name'        => $name,
            'category'    => in_array($category, ['IKU', 'Renstra', 'Fakultas', 'SPMI']) ? $category : 'IKU',
            'formula'     => $formula,
            'unit'        => $unit ?: '%',
            'data_source' => $dataSource,
            'is_active'   => $isActive
        ]);

        // Update or create 2026 target
        $targetModel = new IndicatorTarget();
        $existingTarget = $targetModel->rawFetch("SELECT * FROM indicator_targets WHERE indicator_id = :id AND year = 2026 LIMIT 1", ['id' => $id]);
        if (!empty($existingTarget)) {
            $targetId = $existingTarget[0]['id'];
            $targetModel->update($targetId, ['target_value' => $targetValue]);

            // Recalculate achievement percentage if realization exists
            $realizationModel = new IndicatorRealization();
            $realization = $realizationModel->whereOne('indicator_target_id', $targetId);
            if ($realization) {
                $realVal = (float)$realization['realization_value'];
                $calcTarget = $targetValue > 0 ? $targetValue : 1;
                $rawAch = round(($realVal / $calcTarget) * 100, 2);
                $cappedAch = min(100.0, $rawAch);
                $realizationModel->update($realization['id'], [
                    'achievement_percentage' => $cappedAch
                ]);
            }
        } else {
            $targetModel->create([
                'indicator_id' => $id,
                'faculty_id'   => 1,
                'year'         => 2026,
                'period'       => 'Tahunan',
                'target_value' => $targetValue
            ]);
        }

        AuditService::log('UPDATE', 'indicators', (string)$id, $existing, [
            'code'         => $code,
            'name'         => $name,
            'target_value' => $targetValue
        ]);

        $this->redirect('strategic/indicators', ['success' => "Indikator {$code} berhasil diperbarui."]);
    }

    public function deleteIndicator(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('strategic/indicators', ['error' => 'ID indikator tidak valid.']);
            return;
        }

        $indicatorModel = new Indicator();
        $existing = $indicatorModel->find($id);
        if (!$existing) {
            $this->redirect('strategic/indicators', ['error' => 'Indikator tidak ditemukan.']);
            return;
        }

        $indicatorModel->delete($id);
        AuditService::log('DELETE', 'indicators', (string)$id, $existing, null);

        $this->redirect('strategic/indicators', ['success' => "Indikator {$existing['code']} - {$existing['name']} berhasil dihapus."]);
    }

    public function saveRealization(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $targetId = (int)$this->getPost('target_id', 0);
        $realizationValue = (float)$this->getPost('realization_value', 0);
        $targetValue = (float)$this->getPost('target_value', 1);
        $notes = trim($this->getPost('notes', ''));

        if ($targetValue <= 0) $targetValue = 1;
        $rawAchievement = round(($realizationValue / $targetValue) * 100, 2);
        $achievement = min(100.0, $rawAchievement); // Capped at 100% max if target met/exceeded

        $status = 'Success';
        if ($rawAchievement < 70) {
            $status = 'Critical';
        } elseif ($rawAchievement < 85) {
            $status = 'Warning';
        } elseif ($rawAchievement < 100) {
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
