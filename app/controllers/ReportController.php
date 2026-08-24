<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudyProgram;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\Indicator;
use App\Models\Finance;
use App\Models\Accreditation;
use App\Services\AuditService;

use App\Models\Faculty;
use App\Models\AppSetting;

class ReportController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $programModel = new StudyProgram();
        $lecturerModel = new Lecturer();
        $studentModel = new Student();
        $indicatorModel = new Indicator();
        $financeModel = new Finance();
        $accreditationModel = new Accreditation();
        $facultyModel = new Faculty();
        $settingModel = new AppSetting();

        $programs = $programModel->allWithFaculty();
        $lecturers = $lecturerModel->allWithProgram();
        $students = $studentModel->all();
        $indicators = $indicatorModel->allWithTargetAndRealization(2026);
        $finances = $financeModel->allWithProgram(2026);
        $accreditations = $accreditationModel->allWithProgram();
        $faculty = $facultyModel->find(1);
        $deanName = $settingModel->get('dean_name') ?: ($faculty['dean_name'] ?? 'Dekan FTIK');
        $deanNip = $settingModel->get('dean_nip') ?: '197805122003121002';

        $this->render('reports/index', [
            'title'          => 'Laporan Eksekutif Dekan',
            'programs'       => $programs,
            'lecturers'      => $lecturers,
            'students'       => $students,
            'indicators'     => $indicators,
            'finances'       => $finances,
            'accreditations' => $accreditations,
            'faculty'        => $faculty,
            'deanName'       => $deanName,
            'deanNip'        => $deanNip
        ]);
    }

    public function export(): void {
        $this->requireAuth();
        $type = $this->getQuery('type', 'print'); // 'print' or 'csv'

        $programModel = new StudyProgram();
        $lecturerModel = new Lecturer();
        $studentModel = new Student();
        $indicatorModel = new Indicator();
        $facultyModel = new Faculty();
        $settingModel = new AppSetting();

        $programs = $programModel->allWithFaculty();
        $lecturers = $lecturerModel->allWithProgram();
        $indicators = $indicatorModel->allWithTargetAndRealization(2026);
        $faculty = $facultyModel->find(1);
        $deanName = $settingModel->get('dean_name') ?: ($faculty['dean_name'] ?? 'Dekan FTIK');
        $deanNip = $settingModel->get('dean_nip') ?: '197805122003121002';

        AuditService::log('EXPORT', 'reports', null, null, ['format' => $type]);

        if ($type === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=laporan_eksekutif_deis_' . date('Ymd') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Kode Indikator', 'Nama Indikator', 'Kategori', 'Target', 'Realisasi', 'Capaian (%)', 'Status']);
            foreach ($indicators as $row) {
                fputcsv($output, [
                    $row['code'],
                    $row['name'],
                    $row['category'],
                    $row['target_value'] . ' ' . $row['unit'],
                    $row['realization_value'] . ' ' . $row['unit'],
                    $row['achievement_percentage'] . '%',
                    $row['realization_status']
                ]);
            }
            fclose($output);
            exit;
        }

        // Print Layout
        $this->render('reports/print', [
            'title'      => 'Laporan Eksekutif Dekan Fakultas - Cetak / PDF',
            'programs'   => $programs,
            'lecturers'  => $lecturers,
            'indicators' => $indicators,
            'faculty'    => $faculty,
            'deanName'   => $deanName,
            'deanNip'    => $deanNip
        ], 'layouts/print');
    }
}
