<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Finance;
use App\Models\StudyProgram;

class FinanceController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $financeModel = new Finance();
        $finances = $financeModel->allWithProgram(2026);

        $totalBudget = array_sum(array_column($finances, 'budgeted_amount'));
        $totalRealized = array_sum(array_column($finances, 'realized_amount'));
        $overallAbsorption = $totalBudget > 0 ? round(($totalRealized / $totalBudget) * 100, 1) : 0;

        $this->render('finance/index', [
            'title'             => 'Keuangan & Serapan Anggaran Fakultas',
            'finances'          => $finances,
            'totalBudget'       => $totalBudget,
            'totalRealized'     => $totalRealized,
            'overallAbsorption' => $overallAbsorption
        ]);
    }
}
