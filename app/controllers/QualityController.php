<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SpmiStandard;
use App\Models\AmiAudit;
use App\Models\AmiFinding;
use App\Models\Survey;

class QualityController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $standardModel = new SpmiStandard();
        $standards = $standardModel->all('code ASC');

        // Stats
        $totalStandards = count($standards);
        $achievedCount = count(array_filter($standards, fn($s) => $s['status'] === 'Tercapai'));
        $processCount = count(array_filter($standards, fn($s) => $s['status'] === 'Proses'));
        $unachievedCount = count(array_filter($standards, fn($s) => $s['status'] === 'Belum Tercapai'));

        $this->render('quality/index', [
            'title'           => 'Penjaminan Mutu Internal (SPMI) & Siklus PPEPP',
            'standards'       => $standards,
            'totalStandards'  => $totalStandards,
            'achievedCount'   => $achievedCount,
            'processCount'    => $processCount,
            'unachievedCount' => $unachievedCount
        ]);
    }

    public function ami(): void {
        $this->requireAuth();

        $amiAuditModel = new AmiAudit();
        $amiFindingModel = new AmiFinding();

        $audits = $amiAuditModel->allWithProgram();
        $findings = $amiFindingModel->allWithAuditAndStandard();

        $this->render('quality/ami', [
            'title'    => 'Audit Mutu Internal (AMI) & Temuan KTS/OB',
            'audits'   => $audits,
            'findings' => $findings
        ]);
    }

    public function surveys(): void {
        $this->requireAuth();

        $surveyModel = new Survey();
        $surveys = $surveyModel->all('period_year DESC');

        $this->render('quality/surveys', [
            'title'   => 'Survei Kepuasan Stakeholder Mutu',
            'surveys' => $surveys
        ]);
    }
}
