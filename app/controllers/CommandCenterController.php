<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Approval;
use App\Models\CriticalAlert;
use App\Models\ActionItem;
use App\Models\Accreditation;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\CommandCenterService;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Helpers\AuthHelper;

class CommandCenterController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $attention = CommandCenterService::getMyAttention();
        $criticalAlerts = CommandCenterService::getCriticalAlerts();
        $deadlines = CommandCenterService::getDeadlineRadar();
        $pendingApprovals = CommandCenterService::getPendingApprovals();

        // Problematic Lecturers & Students Details for Quick Action
        $lecturerModel = new Lecturer();
        $studentModel = new Student();
        $actionItemModel = new ActionItem();

        $problematicLecturers = $lecturerModel->rawFetch("
            SELECT l.*, sp.name as program_name 
            FROM lecturers l 
            JOIN study_programs sp ON l.study_program_id = sp.id 
            WHERE l.attendance_percentage < 75.00 OR l.bkd_status = 'Belum Memenuhi'
        ");

        $atRiskStudents = $studentModel->getEarlyWarningList();
        $overdueRtls = $actionItemModel->rawFetch("
            SELECT ai.*, m.title as meeting_title, sp.name as program_name 
            FROM action_items ai
            JOIN meetings m ON ai.meeting_id = m.id
            LEFT JOIN study_programs sp ON ai.study_program_id = sp.id
            WHERE ai.status = 'Terlambat' OR (ai.deadline < CURDATE() AND ai.status NOT IN ('Selesai', 'Dibatalkan'))
        ");

        $this->render('command_center/index', [
            'title'                => 'Command Center Eksekutif',
            'attention'            => $attention,
            'criticalAlerts'       => $criticalAlerts,
            'deadlines'            => $deadlines,
            'pendingApprovals'     => $pendingApprovals,
            'problematicLecturers' => $problematicLecturers,
            'atRiskStudents'       => $atRiskStudents,
            'overdueRtls'          => $overdueRtls
        ]);
    }

    public function approvals(): void {
        $this->requireAuth();

        $user = AuthHelper::user();
        $isDeanOrAdmin = AuthHelper::hasRole(['dekan', 'super_admin', 'developer']);
        $programId = $user['study_program_id'] ?? null;
        $userId = AuthHelper::id();

        $approvalModel = new Approval();
        $approvals = $approvalModel->allWithDetails($userId, $programId, $isDeanOrAdmin);

        $this->render('command_center/approvals', [
            'title'         => 'Pengajuan & Persetujuan Dokumen',
            'approvals'     => $approvals,
            'canApprove'    => $isDeanOrAdmin,
            'user'          => $user
        ]);
    }

    public function createApproval(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $module = trim($this->getPost('module', 'Kegiatan'));
        $title = trim($this->getPost('title', ''));
        $notes = trim($this->getPost('notes', ''));
        $programId = (int)$this->getPost('study_program_id', 0) ?: (AuthHelper::user()['study_program_id'] ?? 1);

        if (empty($title)) {
            $this->redirect('command-center/approvals', ['error' => 'Judul permohonan wajib diisi.']);
        }

        $user = AuthHelper::user();
        $approvalModel = new Approval();
        $newId = $approvalModel->create([
            'study_program_id' => $programId,
            'requester_id'     => AuthHelper::id(),
            'requester_name'   => $user['name'],
            'module'           => $module,
            'title'            => $title,
            'submission_date'  => date('Y-m-d'),
            'status'           => 'Pending',
            'notes'            => $notes
        ]);

        AuditService::log('SUBMIT_APPROVAL', 'approvals', (string)$newId, null, ['title' => $title, 'module' => $module]);
        $this->redirect('command-center/approvals', ['success' => 'Permohonan berhasil diajukan dan sedang menunggu persetujuan Dekan.']);
    }

    public function handleApproval(): void {
        $this->requireAuth();
        $this->requireRole(['dekan', 'super_admin', 'developer']);
        $this->requireCsrf();

        $approvalId = (int)$this->getPost('approval_id', 0);
        $action = $this->getPost('action', ''); // 'Approved' or 'Rejected'
        $notes = trim($this->getPost('notes', ''));

        if (!$approvalId || !in_array($action, ['Approved', 'Rejected'], true)) {
            $this->redirect('command-center/approvals', ['error' => 'Data persetujuan tidak valid.']);
        }

        $approvalModel = new Approval();
        $approval = $approvalModel->find($approvalId);

        if (!$approval) {
            $this->redirect('command-center/approvals', ['error' => 'Data pengajuan tidak ditemukan.']);
        }

        $user = AuthHelper::user();
        $approvalModel->update($approvalId, [
            'status'      => $action,
            'notes'       => $notes,
            'approved_by' => $user['name'],
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        AuditService::log($action === 'Approved' ? 'APPROVE' : 'REJECT', 'approvals', (string)$approvalId, $approval, [
            'status' => $action,
            'notes'  => $notes
        ]);

        // Send notification to requester if exists
        if (!empty($approval['requester_id'])) {
            NotificationService::send(
                (int)$approval['requester_id'],
                "Pengajuan " . ($action === 'Approved' ? 'Disetujui' : 'Ditolak'),
                "Pengajuan '{$approval['title']}' telah {$action} oleh Dekan.",
                $action === 'Approved' ? 'approval' : 'alert',
                'command-center/approvals'
            );
        }

        $this->redirect('command-center/approvals', [
            'success' => "Pengajuan berhasil di-{$action}."
        ]);
    }

    public function resolveAlert(): void {
        $this->requireAuth();
        $this->requireRole(['dekan', 'super_admin', 'developer']);
        $this->requireCsrf();

        $alertId = (int)$this->getPost('alert_id', 0);
        if ($alertId) {
            $alertModel = new CriticalAlert();
            $alertModel->update($alertId, ['is_resolved' => 1]);
            AuditService::log('RESOLVE_ALERT', 'critical_alerts', (string)$alertId);
        }

        $this->redirect('command-center', ['success' => 'Peringatan telah ditandai selesai.']);
    }
}
