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
}
