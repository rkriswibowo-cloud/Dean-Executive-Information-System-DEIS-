<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Accreditation;
use App\Models\StudyProgram;
use App\Services\AuditService;

class AccreditationController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $accreditationModel = new Accreditation();
        $accreditations = $accreditationModel->allWithProgram();
        $studyProgramModel = new StudyProgram();
        $programs = $studyProgramModel->all();

        $this->render('accreditation/index', [
            'title'          => 'Monitoring Akreditasi Program Studi',
            'accreditations' => $accreditations,
            'programs'       => $programs
        ]);
    }

    public function updateProgress(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('accreditation', ['danger' => 'ID Akreditasi tidak valid.']);
        }

        $accreditationModel = new Accreditation();
        $existing = $accreditationModel->find($id);

        if (!$existing) {
            $this->redirect('accreditation', ['danger' => 'Data akreditasi tidak ditemukan.']);
        }

        $institution = trim($this->getPost('institution', $existing['institution'] ?? 'LAM-INFOKOM'));
        $currentGrade = trim($this->getPost('current_grade', $existing['current_grade'] ?? 'Baik Sekali'));
        $targetGrade = trim($this->getPost('target_grade', $existing['target_grade'] ?? 'Unggul'));
        $validUntil = trim($this->getPost('valid_until', $existing['valid_until'] ?? date('Y-m-d')));
        $ledProgress = (float)$this->getPost('led_progress', $existing['led_progress'] ?? 0);
        $lkpsProgress = (float)$this->getPost('lkps_progress', $existing['lkps_progress'] ?? 0);
        $overallProgress = (float)$this->getPost('overall_progress', round(($ledProgress + $lkpsProgress) / 2, 1));
        $status = trim($this->getPost('status', 'Aman'));
        $pic = trim($this->getPost('pic', $existing['pic'] ?? 'Kaprodi'));
        $gapNotes = trim($this->getPost('gap_notes', ''));
        $actionPlan = trim($this->getPost('action_plan', ''));

        // Calculate days remaining
        $daysRemaining = (int)((strtotime($validUntil) - strtotime(date('Y-m-d'))) / 86400);

        // Auto determine status if not manually set to Kritis
        if ($daysRemaining < 90 && $status === 'Aman') {
            $status = 'Kritis';
        } elseif ($daysRemaining < 180 && $status === 'Aman') {
            $status = 'Perhatian';
        }

        $accreditationModel->update($id, [
            'institution'      => $institution,
            'current_grade'    => $currentGrade,
            'target_grade'     => $targetGrade,
            'valid_until'      => $validUntil,
            'days_remaining'   => $daysRemaining,
            'led_progress'     => min(100, max(0, $ledProgress)),
            'lkps_progress'    => min(100, max(0, $lkpsProgress)),
            'overall_progress' => min(100, max(0, $overallProgress)),
            'status'           => $status,
            'pic'              => $pic,
            'gap_notes'        => $gapNotes,
            'action_plan'      => $actionPlan
        ]);

        // Also update study_programs table accreditation grade and expire date
        if (!empty($existing['study_program_id'])) {
            $spModel = new StudyProgram();
            $spModel->update((int)$existing['study_program_id'], [
                'accreditation_status' => $currentGrade,
                'accreditation_expire' => $validUntil
            ]);
        }

        AuditService::log('UPDATE_ACCREDITATION', 'accreditations', (string)$id, $existing, [
            'led_progress'     => $ledProgress,
            'lkps_progress'    => $lkpsProgress,
            'overall_progress' => $overallProgress,
            'status'           => $status,
            'valid_until'      => $validUntil
        ]);

        $this->redirect('accreditation', ['success' => 'Perkembangan akreditasi & rencana aksi berhasil diperbarui!']);
    }
}
