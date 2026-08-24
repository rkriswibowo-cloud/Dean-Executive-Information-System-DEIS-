<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingDocument;
use App\Models\ActionItem;
use App\Models\User;
use App\Helpers\UploadHelper;
use App\Helpers\AuthHelper;
use App\Services\AuditService;

class MeetingController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $meetingModel = new Meeting();
        $meetings = $meetingModel->allWithDetails();

        $this->render('meetings/index', [
            'title'    => 'Rapat & Tata Kelola Digital Fakultas',
            'meetings' => $meetings
        ]);
    }

    public function create(): void {
        $this->requireAuth();
        $userModel = new User();
        $users = $userModel->allWithRole();

        $this->render('meetings/create', [
            'title' => 'Buat Jadwal Rapat Baru',
            'users' => $users
        ]);
    }

    public function store(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $title = trim($this->getPost('title', ''));
        $type = $this->getPost('type', 'Rapat Pimpinan');
        $meetingDate = $this->getPost('meeting_date', '');
        $startTime = $this->getPost('start_time', '');
        $location = trim($this->getPost('location', ''));
        $chairpersonId = (int)$this->getPost('chairperson_id', 0) ?: null;
        $secretaryId = (int)$this->getPost('secretary_id', 0) ?: null;
        $agenda = trim($this->getPost('agenda', ''));

        $meetingNumber = 'RAPAT-' . date('Y-m-d') . '-' . rand(100, 999);

        $meetingModel = new Meeting();
        $meetingId = $meetingModel->create([
            'meeting_number' => $meetingNumber,
            'title'          => $title,
            'type'           => $type,
            'meeting_date'   => $meetingDate,
            'start_time'     => $startTime,
            'location'       => $location,
            'chairperson_id' => $chairpersonId,
            'secretary_id'   => $secretaryId,
            'agenda'         => $agenda,
            'status'         => 'Terjadwal'
        ]);

        AuditService::log('CREATE', 'meetings', (string)$meetingId, null, ['title' => $title, 'number' => $meetingNumber]);

        $this->redirect('meetings/detail?id=' . $meetingId, [
            'success' => 'Jadwal rapat berhasil dibuat. Silakan kelola paket dokumen digital dan peserta.'
        ]);
    }

    public function detail(): void {
        $this->requireAuth();
        $id = (int)$this->getQuery('id', 0);

        $meetingModel = new Meeting();
        $meeting = $meetingModel->getDigitalPacket($id);

        if (empty($meeting)) {
            $this->redirect('meetings', ['error' => 'Data rapat tidak ditemukan.']);
        }

        $userModel = new User();
        $users = $userModel->allWithRole();

        $this->render('meetings/detail', [
            'title'   => 'Paket Digital Rapat - ' . $meeting['title'],
            'meeting' => $meeting,
            'users'   => $users
        ]);
    }

    public function uploadDocument(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $meetingId = (int)$this->getPost('meeting_id', 0);
        $documentType = $this->getPost('document_type', 'Materi');
        $fileTitle = trim($this->getPost('file_title', ''));

        if (!$meetingId || empty($_FILES['document_file']['name'])) {
            $this->redirect('meetings/detail?id=' . $meetingId, ['error' => 'File dokumen wajib dipilih.']);
        }

        try {
            $uploaded = UploadHelper::upload($_FILES['document_file'], 'meetings', $meetingId);

            $docModel = new MeetingDocument();
            $docModel->create([
                'meeting_id'    => $meetingId,
                'document_type' => $documentType,
                'file_title'    => $fileTitle ?: $uploaded['original_name'],
                'file_path'     => $uploaded['file_path'],
                'file_size'     => $uploaded['file_size'],
                'uploaded_by'   => AuthHelper::user()['name'] ?? 'User'
            ]);

            AuditService::log('UPLOAD_DOCUMENT', 'meeting_documents', (string)$meetingId, null, ['title' => $fileTitle]);
            $this->redirect('meetings/detail?id=' . $meetingId, ['success' => 'Dokumen rapat berhasil diunggah.']);
        } catch (\Exception $e) {
            $this->redirect('meetings/detail?id=' . $meetingId, ['error' => 'Gagal unggah: ' . $e->getMessage()]);
        }
    }

    public function rtl(): void {
        $this->requireAuth();

        $actionItemModel = new ActionItem();
        $actionItems = $actionItemModel->allWithMeetingAndProgram();

        $this->render('meetings/rtl', [
            'title'       => 'Monitoring & Tracking RTL Rapat (Tindak Lanjut)',
            'actionItems' => $actionItems
        ]);
    }

    public function updateRtl(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $rtlId = (int)$this->getPost('rtl_id', 0);
        $status = $this->getPost('status', 'Proses');
        $progress = (float)$this->getPost('progress_percentage', 0);
        $notes = trim($this->getPost('notes', ''));

        $rtlModel = new ActionItem();
        $existing = $rtlModel->find($rtlId);

        if (!$existing) {
            $this->redirect('meetings/rtl', ['error' => 'Data RTL tidak ditemukan.']);
        }

        $user = AuthHelper::user();
        $updateData = [
            'status'              => $status,
            'progress_percentage' => $progress,
            'notes'               => $notes
        ];

        if ($status === 'Selesai' && empty($existing['verified_by'])) {
            $updateData['verified_by'] = $user['name'];
            $updateData['verified_at'] = date('Y-m-d H:i:s');
        }

        $rtlModel->update($rtlId, $updateData);
        AuditService::log('UPDATE_RTL', 'action_items', (string)$rtlId, $existing, $updateData);

        $this->redirect('meetings/rtl', ['success' => 'Status tindak lanjut RTL berhasil diperbarui.']);
    }
}
