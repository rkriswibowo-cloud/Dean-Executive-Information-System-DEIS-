<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cooperation;
use App\Services\AuditService;

class CooperationController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $cooperationModel = new Cooperation();
        $cooperations = $cooperationModel->allWithDaysRemaining();

        $totalActive = count(array_filter($cooperations, fn($c) => $c['status'] === 'Aktif'));
        $totalExpiring = count(array_filter($cooperations, fn($c) => $c['status'] === 'Akan Berakhir'));
        $totalActivities = array_sum(array_column($cooperations, 'real_activities_count'));

        $this->render('cooperation/index', [
            'title'           => 'Kerja Sama & Kemitraan Strategis',
            'cooperations'    => $cooperations,
            'totalActive'     => $totalActive,
            'totalExpiring'   => $totalExpiring,
            'totalActivities' => $totalActivities
        ]);
    }

    public function create(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $partnerName = trim($this->getPost('partner_name', ''));
        $type = $this->getPost('type', 'MoA');
        $level = $this->getPost('level', 'Nasional');
        $scope = trim($this->getPost('scope', ''));
        $startDate = $this->getPost('start_date', '');
        $endDate = $this->getPost('end_date', '');
        $picInternal = trim($this->getPost('pic_internal', ''));
        $realActivities = (int)$this->getPost('real_activities_count', 0);

        if (empty($partnerName)) {
            $this->redirect('cooperations', ['danger' => 'Nama mitra kerja sama wajib diisi.']);
        }

        $daysRemaining = (int)((strtotime($endDate) - strtotime(date('Y-m-d'))) / 86400);
        $status = $daysRemaining < 0 ? 'Kadaluarsa' : ($daysRemaining <= 30 ? 'Akan Berakhir' : 'Aktif');

        $cooperationModel = new Cooperation();
        $id = $cooperationModel->create([
            'partner_name'          => $partnerName,
            'type'                  => $type,
            'level'                 => $level,
            'scope'                 => $scope,
            'start_date'            => $startDate,
            'end_date'              => $endDate,
            'pic_internal'          => $picInternal,
            'real_activities_count' => $realActivities,
            'status'                => $status
        ]);

        AuditService::log('CREATE', 'cooperations', (string)$id, null, ['partner_name' => $partnerName]);
        $this->redirect('cooperations', ['success' => 'Dokumen kerja sama baru berhasil ditambahkan.']);
    }

    public function update(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('cooperations', ['danger' => 'ID Kerja sama tidak valid.']);
        }

        $cooperationModel = new Cooperation();
        $existing = $cooperationModel->find($id);
        if (!$existing) {
            $this->redirect('cooperations', ['danger' => 'Data kerja sama tidak ditemukan.']);
        }

        $partnerName = trim($this->getPost('partner_name', $existing['partner_name']));
        $type = $this->getPost('type', $existing['type']);
        $level = $this->getPost('level', $existing['level']);
        $scope = trim($this->getPost('scope', $existing['scope']));
        $startDate = $this->getPost('start_date', $existing['start_date']);
        $endDate = $this->getPost('end_date', $existing['end_date']);
        $picInternal = trim($this->getPost('pic_internal', $existing['pic_internal']));
        $realActivities = (int)$this->getPost('real_activities_count', $existing['real_activities_count'] ?? 0);
        $status = $this->getPost('status', $existing['status']);

        $daysRemaining = (int)((strtotime($endDate) - strtotime(date('Y-m-d'))) / 86400);
        if ($daysRemaining < 0) {
            $status = 'Kadaluarsa';
        } elseif ($daysRemaining <= 30 && $status === 'Aktif') {
            $status = 'Akan Berakhir';
        }

        $cooperationModel->update($id, [
            'partner_name'          => $partnerName,
            'type'                  => $type,
            'level'                 => $level,
            'scope'                 => $scope,
            'start_date'            => $startDate,
            'end_date'              => $endDate,
            'pic_internal'          => $picInternal,
            'real_activities_count' => $realActivities,
            'status'                => $status
        ]);

        AuditService::log('UPDATE', 'cooperations', (string)$id, $existing, [
            'partner_name'          => $partnerName,
            'real_activities_count' => $realActivities,
            'status'                => $status
        ]);

        $this->redirect('cooperations', ['success' => "Data kemitraan & aktivitas '{$partnerName}' berhasil diperbarui."]);
    }

    public function delete(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('cooperations', ['danger' => 'ID tidak valid.']);
        }

        $cooperationModel = new Cooperation();
        $existing = $cooperationModel->find($id);
        if ($existing) {
            $cooperationModel->delete($id);
            AuditService::log('DELETE', 'cooperations', (string)$id, null, ['partner_name' => $existing['partner_name'] ?? '']);
            $this->redirect('cooperations', ['success' => 'Dokumen kerja sama berhasil dihapus.']);
        }

        $this->redirect('cooperations', ['danger' => 'Gagal menghapus data kerja sama.']);
    }
}
