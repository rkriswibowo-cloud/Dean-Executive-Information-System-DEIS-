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

        $cooperationModel = new Cooperation();
        $id = $cooperationModel->create([
            'partner_name'  => $partnerName,
            'type'          => $type,
            'level'         => $level,
            'scope'         => $scope,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'pic_internal'  => $picInternal,
            'status'        => 'Aktif'
        ]);

        AuditService::log('CREATE', 'cooperations', (string)$id, null, ['partner_name' => $partnerName]);
        $this->redirect('cooperations', ['success' => 'Dokumen kerja sama baru berhasil ditambahkan.']);
    }
}
