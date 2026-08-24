<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\Guidance;
use App\Models\StudyProgram;
use App\Models\AcademicYear;

class AcademicController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $classModel = new ClassModel();
        $ayModel = new AcademicYear();
        $activeAy = $ayModel->getActive();

        $classes = $classModel->allWithDetails($activeAy ? (int)$activeAy['id'] : null);
        $programModel = new StudyProgram();
        $programs = $programModel->all();

        // Calculate statistics
        $totalClasses = count($classes);
        $problemClasses = count(array_filter($classes, fn($c) => $c['problem_flag'] == 1));
        
        $totalHeld = array_sum(array_column($classes, 'total_held_meetings'));
        $totalPlanned = array_sum(array_column($classes, 'total_planned_meetings')) ?: 1;
        $realizationRate = round(($totalHeld / $totalPlanned) * 100, 1);

        $avgAttendance = count($classes) > 0 ? round(array_sum(array_column($classes, 'average_attendance')) / count($classes), 1) : 0;

        $this->render('academic/index', [
            'title'           => 'Monitoring Perkuliahan & Akademik',
            'classes'         => $classes,
            'programs'        => $programs,
            'activeAy'        => $activeAy,
            'totalClasses'    => $totalClasses,
            'problemClasses'  => $problemClasses,
            'realizationRate' => $realizationRate,
            'avgAttendance'   => $avgAttendance
        ]);
    }

    public function courses(): void {
        $this->requireAuth();
        $courseModel = new Course();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $courses = $courseModel->allWithLecturerAndProgram($filterProgram ?: null);
        $programs = $programModel->all();

        $this->render('academic/courses', [
            'title'         => 'Data Kurikulum & Mata Kuliah',
            'courses'       => $courses,
            'programs'      => $programs,
            'filterProgram' => $filterProgram
        ]);
    }

    public function guidance(): void {
        $this->requireAuth();
        $guidanceModel = new Guidance();

        $filterType = $this->getQuery('type', '');
        $guidances = $guidanceModel->allWithDetails($filterType ?: null);

        $this->render('academic/guidance', [
            'title'      => 'Monitoring Bimbingan (DPA / Skripsi / MBKM)',
            'guidances'  => $guidances,
            'filterType' => $filterType
        ]);
    }

    public function actionClass(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $classId = (int)$this->getPost('class_id', 0);
        $actionType = $this->getPost('action_type', ''); // 'resolve' or 'disposition'
        $notes = trim($this->getPost('notes', ''));

        if ($classId <= 0) {
            $this->redirect('academic', ['danger' => 'ID Kelas tidak valid.']);
        }

        $classModel = new ClassModel();
        if ($actionType === 'resolve') {
            $classModel->update($classId, [
                'problem_flag'  => 0,
                'problem_notes' => null
            ]);
            \App\Services\AuditService::log('EVAL_CLASS_RESOLVED', 'classes', (string)$classId, null, ['status' => 'resolved', 'notes' => $notes]);
            $this->redirect('academic', ['success' => 'Status masalah kelas berhasil diselesaikan dan dinormalkan.']);
        } else {
            $classModel->update($classId, [
                'problem_flag'  => 1,
                'problem_notes' => $notes ?: 'Instruksi / Disposisi Dekanat: ' . date('d/m/Y')
            ]);
            \App\Services\AuditService::log('DISPOSITION_CLASS', 'classes', (string)$classId, null, ['notes' => $notes]);
            $this->redirect('academic', ['success' => 'Disposisi & Instruksi Dekanat berhasil diterbitkan dan dicatat dalam audit trail.']);
        }
    }

    public function actionRps(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $courseId = (int)$this->getPost('course_id', 0);
        $status = $this->getPost('status', 'Lengkap'); // 'Lengkap', 'Revisi', 'Belum Lengkap'
        $notes = trim($this->getPost('notes', ''));

        if ($courseId <= 0) {
            $this->redirect('academic/courses', ['danger' => 'ID Mata Kuliah tidak valid.']);
        }

        $courseModel = new Course();
        $courseModel->update($courseId, [
            'rps_status' => $status
        ]);

        \App\Services\AuditService::log('APPROVE_RPS', 'courses', (string)$courseId, null, ['status' => $status, 'notes' => $notes]);
        $this->redirect('academic/courses', ['success' => "Status RPS berhasil diperbarui menjadi '{$status}' oleh Dekanat."]);
    }

    public function actionGuidance(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $guidanceId = (int)$this->getPost('guidance_id', 0);
        $status = $this->getPost('status', 'Aktif');
        $progress = (float)$this->getPost('progress_percentage', 0);
        $notes = trim($this->getPost('notes', ''));

        if ($guidanceId <= 0) {
            $this->redirect('academic/guidance', ['danger' => 'ID Bimbingan tidak valid.']);
        }

        $guidanceModel = new Guidance();
        $guidanceModel->update($guidanceId, [
            'status' => $status,
            'progress_percentage' => min(100, max(0, $progress))
        ]);

        \App\Services\AuditService::log('INTERVENE_GUIDANCE', 'guidances', (string)$guidanceId, null, ['status' => $status, 'progress' => $progress, 'notes' => $notes]);
        $this->redirect('academic/guidance', ['success' => "Aksi intervensi bimbingan mahasiswa berhasil disimpan."]);
    }
}
