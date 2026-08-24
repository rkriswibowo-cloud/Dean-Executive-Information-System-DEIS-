<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Finance;
use App\Models\StudyProgram;
use App\Services\AuditService;

class FinanceController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $financeModel = new Finance();
        $studyProgramModel = new StudyProgram();
        $year = (int)$this->getQuery('year', 2026);

        $finances = $financeModel->allWithProgram($year);
        $programs = $studyProgramModel->all();

        $totalBudget = array_sum(array_column($finances, 'budgeted_amount'));
        $totalRealized = array_sum(array_column($finances, 'realized_amount'));
        $overallAbsorption = $totalBudget > 0 ? round(($totalRealized / $totalBudget) * 100, 1) : 0;

        $this->render('finance/index', [
            'title'             => 'Keuangan & Serapan Anggaran Fakultas',
            'finances'          => $finances,
            'programs'          => $programs,
            'totalBudget'       => $totalBudget,
            'totalRealized'     => $totalRealized,
            'overallAbsorption' => $overallAbsorption,
            'selectedYear'      => $year
        ]);
    }

    public function create(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $title = trim($this->getPost('title', ''));
        $category = $this->getPost('category', 'RKA Operasional');
        $studyProgramId = (int)$this->getPost('study_program_id', 0) ?: null;
        $budgetedAmount = (float)$this->getPost('budgeted_amount', 0);
        $realizedAmount = (float)$this->getPost('realized_amount', 0);
        $fiscalYear = (int)$this->getPost('fiscal_year', 2026);

        if (empty($title) || $budgetedAmount <= 0) {
            $this->redirect('finances', ['danger' => 'Nama pos anggaran dan pagu anggaran wajib diisi.']);
        }

        $absorption = $budgetedAmount > 0 ? round(($realizedAmount / $budgetedAmount) * 100, 1) : 0;
        $status = $absorption >= 85 ? 'Optimal' : ($absorption >= 60 ? 'Cukup' : ($absorption > 100 ? 'Overbudget' : 'Rendah'));

        $financeModel = new Finance();
        $newId = $financeModel->create([
            'faculty_id'            => 1,
            'study_program_id'      => $studyProgramId,
            'fiscal_year'           => $fiscalYear,
            'category'              => $category,
            'title'                 => $title,
            'budgeted_amount'       => $budgetedAmount,
            'realized_amount'       => $realizedAmount,
            'absorption_percentage' => $absorption,
            'status'                => $status
        ]);

        AuditService::log('CREATE', 'finances', (string)$newId, null, ['title' => $title, 'budget' => $budgetedAmount]);
        $this->redirect('finances', ['success' => 'Pos anggaran baru berhasil ditambahkan ke RKA.']);
    }

    public function update(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('finances', ['danger' => 'ID Anggaran tidak valid.']);
        }

        $financeModel = new Finance();
        $existing = $financeModel->find($id);
        if (!$existing) {
            $this->redirect('finances', ['danger' => 'Data anggaran tidak ditemukan.']);
        }

        $title = trim($this->getPost('title', $existing['title']));
        $category = $this->getPost('category', $existing['category']);
        $studyProgramId = (int)$this->getPost('study_program_id', 0) ?: null;
        $budgetedAmount = (float)$this->getPost('budgeted_amount', $existing['budgeted_amount']);
        $realizedAmount = (float)$this->getPost('realized_amount', $existing['realized_amount']);
        $fiscalYear = (int)$this->getPost('fiscal_year', $existing['fiscal_year']);

        $absorption = $budgetedAmount > 0 ? round(($realizedAmount / $budgetedAmount) * 100, 1) : 0;
        $status = $absorption > 100 ? 'Overbudget' : ($absorption >= 85 ? 'Optimal' : ($absorption >= 60 ? 'Cukup' : 'Rendah'));

        $financeModel->update($id, [
            'study_program_id'      => $studyProgramId,
            'fiscal_year'           => $fiscalYear,
            'category'              => $category,
            'title'                 => $title,
            'budgeted_amount'       => $budgetedAmount,
            'realized_amount'       => $realizedAmount,
            'absorption_percentage' => $absorption,
            'status'                => $status
        ]);

        AuditService::log('UPDATE', 'finances', (string)$id, $existing, [
            'title'                 => $title,
            'budgeted_amount'       => $budgetedAmount,
            'realized_amount'       => $realizedAmount,
            'absorption_percentage' => $absorption
        ]);

        $this->redirect('finances', ['success' => "Evaluasi dan realisasi pos anggaran '{$title}' berhasil diperbarui."]);
    }

    public function delete(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id <= 0) {
            $this->redirect('finances', ['danger' => 'ID tidak valid.']);
        }

        $financeModel = new Finance();
        $existing = $financeModel->find($id);
        if ($existing) {
            $financeModel->delete($id);
            AuditService::log('DELETE', 'finances', (string)$id, null, ['title' => $existing['title'] ?? '']);
            $this->redirect('finances', ['success' => 'Pos anggaran berhasil dihapus.']);
        }

        $this->redirect('finances', ['danger' => 'Gagal menghapus data anggaran.']);
    }
}
