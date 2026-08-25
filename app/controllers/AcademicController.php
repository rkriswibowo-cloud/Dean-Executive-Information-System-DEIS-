<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\ClassModel;
use App\Models\Guidance;
use App\Models\StudyProgram;
use App\Models\AcademicYear;

use App\Models\PracticumModule;
use App\Models\Approval;
use App\Services\AuditService;

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

    /**
     * Cek Modul Praktikum & Lab (Dekan Executive View)
     */
    public function practicum(): void {
        $this->requireAuth();

        $practicumModel = new PracticumModule();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterSemester = (int)$this->getQuery('semester', 0);
        $filterStatus = trim($this->getQuery('status', ''));

        $modules = $practicumModel->allWithDetails(
            $filterProgram ?: null,
            $filterSemester ?: null,
            $filterStatus ?: null
        );

        $programs = $programModel->all();
        $prodiSummary = $practicumModel->getSummaryByProdi();
        $prodiTotals = $practicumModel->getProdiTotals();
        $stats = $practicumModel->getOverallStats();

        $this->render('academic/practicum', [
            'title'          => 'Cek & Verifikasi Modul Praktikum Laboratorium',
            'modules'        => $modules,
            'programs'       => $programs,
            'prodiSummary'   => $prodiSummary,
            'prodiTotals'    => $prodiTotals,
            'stats'          => $stats,
            'filterProgram'  => $filterProgram,
            'filterSemester' => $filterSemester,
            'filterStatus'   => $filterStatus
        ]);
    }

    /**
     * Konfirmasi Dekan ke Kaprodi
     */
    public function confirmPracticum(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('practicum_id', 0);
        $notes = trim($this->getPost('dekan_notes', ''));

        if ($id <= 0) {
            $this->redirect('academic/practicum', ['danger' => 'ID Modul Praktikum tidak valid.']);
        }

        $practicumModel = new PracticumModule();
        $module = $practicumModel->findWithDetails($id);

        if (!$module) {
            $this->redirect('academic/practicum', ['danger' => 'Data Modul Praktikum tidak ditemukan.']);
        }

        $practicumModel->confirmToKaprodi($id, $notes ?: 'Dekan meminta tindak lanjut kelengkapan modul praktikum ' . $module['course_name']);

        // Log audit trail
        AuditService::log(
            'CONFIRM_PRACTICUM_KAPRODI',
            'practicum_modules',
            (string)$id,
            null,
            ['notes' => $notes, 'course' => $module['course_name'], 'prodi' => $module['program_name']]
        );

        $this->redirect('academic/practicum', [
            'success' => "Catatan verifikasi berhasil diteruskan dan dikonfirmasikan ke Kaprodi {$module['program_name']}."
        ]);
    }

    /**
     * Update Progres Modul Praktikum
     */
    public function updatePracticum(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('practicum_id', 0);
        if ($id <= 0) {
            $this->redirect('academic/practicum', ['danger' => 'ID Modul Praktikum tidak valid.']);
        }

        $isReady = (int)$this->getPost('is_module_ready', 1);
        $status = trim($this->getPost('status', ''));
        if (empty($status)) {
            $status = ($isReady === 1) ? 'Terpenuhi' : 'Belum Lengkap';
        }

        $data = [
            'is_module_ready'   => $isReady,
            'module_file'       => trim($this->getPost('module_file', '')),
            'lab_name'          => trim($this->getPost('lab_name', '')),
            'lecturer_name'     => trim($this->getPost('lecturer_name', '')),
            'assistant_name'    => trim($this->getPost('assistant_name', '')),
            'logbook_status'    => trim($this->getPost('logbook_status', 'Lengkap')),
            'status'            => $status,
            'dekan_notes'       => trim($this->getPost('dekan_notes', '')),
            'kaprodi_feedback'  => trim($this->getPost('kaprodi_feedback', ''))
        ];

        $practicumModel = new PracticumModule();
        $practicumModel->updateProgress($id, $data);

        AuditService::log('UPDATE_PRACTICUM_MODULE', 'practicum_modules', (string)$id, null, $data);

        $this->redirect('academic/practicum', ['success' => 'Status dan kelengkapan modul praktikum berhasil diperbarui.']);
    }

    /**
     * Tambah Matakuliah Praktikum Baru
     */
    public function createPracticum(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $studyProgramId = (int)$this->getPost('study_program_id', 1);
        $semester = (int)$this->getPost('semester', 1);
        $courseCode = trim($this->getPost('course_code', ''));
        $courseName = trim($this->getPost('course_name', ''));
        $labName = trim($this->getPost('lab_name', ''));
        $lecturerName = trim($this->getPost('lecturer_name', ''));
        $assistantName = trim($this->getPost('assistant_name', ''));
        $isReady = (int)$this->getPost('is_module_ready', 1);
        $moduleFile = trim($this->getPost('module_file', ''));
        $dekanNotes = trim($this->getPost('dekan_notes', ''));

        if (empty($courseCode) || empty($courseName) || empty($labName)) {
            $this->redirect('academic/practicum', ['danger' => 'Kode Matakuliah, Nama Matakuliah, dan Laboratorium wajib diisi.']);
        }

        $status = ($isReady === 1) ? 'Terpenuhi' : 'Belum Lengkap';

        $practicumModel = new PracticumModule();
        $practicumModel->create([
            'study_program_id'  => $studyProgramId,
            'academic_year_id'  => 1,
            'semester'          => $semester,
            'course_code'       => $courseCode,
            'course_name'       => $courseName,
            'sks_lab'           => 1,
            'lab_name'          => $labName,
            'lecturer_name'     => $lecturerName,
            'assistant_name'    => $assistantName,
            'is_module_ready'   => $isReady,
            'module_file'       => $moduleFile ?: ($isReady ? 'Modul_' . $courseCode . '.pdf' : null),
            'logbook_status'    => 'Lengkap',
            'status'            => $status,
            'dekan_notes'       => $dekanNotes
        ]);

        AuditService::log('CREATE_PRACTICUM_MODULE', 'practicum_modules', $courseCode, null, ['name' => $courseName]);

        $this->redirect('academic/practicum', ['success' => 'Matakuliah Praktikum baru berhasil ditambahkan ke pemantauan Dekan.']);
    }

    /**
     * Hapus Modul Praktikum
     */
    public function deletePracticum(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('practicum_id', 0);
        if ($id <= 0) {
            $this->redirect('academic/practicum', ['danger' => 'ID Modul Praktikum tidak valid.']);
        }

        $practicumModel = new PracticumModule();
        $practicumModel->delete($id);

        AuditService::log('DELETE_PRACTICUM_MODULE', 'practicum_modules', (string)$id, null, []);

        $this->redirect('academic/practicum', ['success' => 'Data praktikum berhasil dihapus.']);
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
            AuditService::log('EVAL_CLASS_RESOLVED', 'classes', (string)$classId, null, ['status' => 'resolved', 'notes' => $notes]);
            $this->redirect('academic', ['success' => 'Status masalah kelas berhasil diselesaikan dan dinormalkan.']);
        } else {
            $classModel->update($classId, [
                'problem_flag'  => 1,
                'problem_notes' => $notes ?: 'Instruksi / Disposisi Dekanat: ' . date('d/m/Y')
            ]);
            AuditService::log('DISPOSITION_CLASS', 'classes', (string)$classId, null, ['notes' => $notes]);
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

        AuditService::log('APPROVE_RPS', 'courses', (string)$courseId, null, ['status' => $status, 'notes' => $notes]);
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

        AuditService::log('INTERVENE_GUIDANCE', 'guidances', (string)$guidanceId, null, ['status' => $status, 'progress' => $progress, 'notes' => $notes]);
        $this->redirect('academic/guidance', ['success' => "Aksi intervensi bimbingan mahasiswa berhasil disimpan."]);
    }
}
