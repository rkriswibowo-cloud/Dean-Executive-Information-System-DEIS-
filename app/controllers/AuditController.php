<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

class AuditController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'dekan', 'developer']);

        $filterModule = $this->getQuery('module', '');
        $filterAction = $this->getQuery('action', '');

        $auditModel = new AuditLog();
        $logs = $auditModel->allWithFilter($filterModule ?: null, $filterAction ?: null, 150);

        $this->render('audit/index', [
            'title'        => 'Audit Trail & Log Aktivitas Sistem',
            'logs'         => $logs,
            'filterModule' => $filterModule,
            'filterAction' => $filterAction
        ]);
    }
}
