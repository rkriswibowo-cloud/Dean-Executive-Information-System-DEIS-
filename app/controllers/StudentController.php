<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\Survey;

class StudentController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $studentModel = new Student();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterStatus = $this->getQuery('status', 'Aktif');

        $students = $studentModel->allWithProgram($filterProgram ?: null);
        if ($filterStatus) {
            $students = array_filter($students, fn($s) => $s['status'] === $filterStatus);
        }

        $programs = $programModel->all();

        // Statistics
        $totalStudents = count($students);
        $criticalCount = count(array_filter($students, fn($s) => $s['risk_status'] === 'Critical'));
        $warningCount = count(array_filter($students, fn($s) => $s['risk_status'] === 'Warning'));
        $scholarshipCount = count(array_filter($students, fn($s) => !empty($s['scholarship'])));

        $this->render('students/index', [
            'title'            => 'Data Mahasiswa & Kemahasiswaan',
            'students'         => $students,
            'programs'         => $programs,
            'filterProgram'    => $filterProgram,
            'filterStatus'     => $filterStatus,
            'totalStudents'    => $totalStudents,
            'criticalCount'    => $criticalCount,
            'warningCount'     => $warningCount,
            'scholarshipCount' => $scholarshipCount
        ]);
    }

    public function earlyWarning(): void {
        $this->requireAuth();

        $studentModel = new Student();
        $atRiskStudents = $studentModel->getEarlyWarningList();

        $this->render('students/early_warning', [
            'title'          => 'Early Warning System (Mahasiswa Berisiko)',
            'atRiskStudents' => $atRiskStudents
        ]);
    }

    public function alumni(): void {
        $this->requireAuth();

        $studentModel = new Student();
        $surveyModel = new Survey();

        $alumni = $studentModel->where('status', 'Lulus');
        $alumniSurveys = $surveyModel->where('category', 'Alumni');
        $employerSurveys = $surveyModel->where('category', 'Pengguna Lulusan');

        $this->render('students/alumni', [
            'title'           => 'Tracer Study, Alumni & Feedback Industri',
            'alumni'          => $alumni,
            'alumniSurveys'   => $alumniSurveys,
            'employerSurveys' => $employerSurveys
        ]);
    }

    public function actionStudent(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $studentId = (int)$this->getPost('student_id', 0);
        $riskStatus = $this->getPost('risk_status', 'Normal');
        $actionNote = trim($this->getPost('action_notes', ''));

        if ($studentId <= 0) {
            $this->redirect('students', ['danger' => 'ID Mahasiswa tidak valid.']);
        }

        $studentModel = new Student();
        $student = $studentModel->find($studentId);

        $studentModel->update($studentId, [
            'risk_status' => $riskStatus,
            'risk_reason' => $actionNote ?: ($student['risk_reason'] ?? 'Tindakan Dekanat')
        ]);

        \App\Services\AuditService::log('INTERVENE_STUDENT_EWS', 'students', (string)$studentId, $student, [
            'risk_status'  => $riskStatus,
            'action_notes' => $actionNote
        ]);

        $this->redirect('students', ['success' => "Intervensi / Tindakan Dekanat untuk mahasiswa berhasil diterbitkan dan dicatat."]);
    }
}
