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
use App\Models\ClassMeeting;
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

    /**
     * Halaman Detail Jurnal Pertemuan Perkuliahan per Kelas
     */
    public function classDetail(): void {
        $this->requireAuth();

        $classId = (int)$this->getQuery('id', 0);
        if ($classId <= 0) {
            $this->redirect('academic', ['danger' => 'Kelas tidak ditemukan.']);
        }

        $classModel = new ClassModel();
        $class = $classModel->findWithDetails($classId);
        if (!$class) {
            $this->redirect('academic', ['danger' => 'Data kelas perkuliahan tidak valid.']);
        }

        $meetingModel = new ClassMeeting();
        $meetings = $meetingModel->byClass($classId);
        $summary = $meetingModel->getSummaryByClass($classId);

        $this->render('academic/class_detail', [
            'title'    => 'Log & Jurnal Pertemuan: ' . $class['course_name'] . ' (' . $class['class_name'] . ')',
            'class'    => $class,
            'meetings' => $meetings,
            'summary'  => $summary
        ]);
    }

    /**
     * Simpan / Tambah Log Pertemuan Baru
     */
    public function createMeeting(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $classId = (int)$this->getPost('class_id', 0);
        $meetingNo = (int)$this->getPost('meeting_no', 1);
        $meetingDate = $this->getPost('meeting_date', date('Y-m-d'));
        $startTime = $this->getPost('start_time', '08:00');
        $endTime = $this->getPost('end_time', '10:30');
        $room = trim($this->getPost('room', ''));
        $topic = trim($this->getPost('topic', ''));
        $subtopic = trim($this->getPost('subtopic', ''));
        $deliveryMethod = $this->getPost('delivery_method', 'Tatap Muka');
        $present = (int)$this->getPost('student_present', 0);
        $total = (int)$this->getPost('student_total', 35);
        $status = $this->getPost('status', 'Terlaksana');
        $notes = trim($this->getPost('lecturer_notes', ''));

        if ($classId <= 0 || empty($topic)) {
            $this->redirect('academic/class-detail?id=' . $classId, ['danger' => 'Topik perkuliahan wajib diisi.']);
        }

        $pct = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        $meetingModel = new ClassMeeting();
        $meetingModel->create([
            'class_id' => $classId,
            'meeting_no' => $meetingNo,
            'meeting_date' => $meetingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room' => $room,
            'topic' => $topic,
            'subtopic' => $subtopic,
            'delivery_method' => $deliveryMethod,
            'student_present' => $present,
            'student_total' => $total,
            'attendance_percentage' => $pct,
            'status' => $status,
            'lecturer_notes' => $notes
        ]);

        $this->syncClassStats($classId);
        AuditService::log('CREATE_MEETING', 'class_meetings', (string)$classId, null, ['meeting_no' => $meetingNo, 'topic' => $topic]);
        $this->redirect('academic/class-detail?id=' . $classId, ['success' => "Pertemuan ke-{$meetingNo} berhasil dicatat dalam log perkuliahan."]);
    }

    /**
     * Update Log Pertemuan
     */
    public function updateMeeting(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $meetingId = (int)$this->getPost('meeting_id', 0);
        $classId = (int)$this->getPost('class_id', 0);

        if ($meetingId <= 0 || $classId <= 0) {
            $this->redirect('academic', ['danger' => 'Data pertemuan tidak valid.']);
        }

        $present = (int)$this->getPost('student_present', 0);
        $total = (int)$this->getPost('student_total', 35);
        $pct = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        $meetingModel = new ClassMeeting();
        $meetingModel->update($meetingId, [
            'meeting_date' => $this->getPost('meeting_date', date('Y-m-d')),
            'start_time' => $this->getPost('start_time', '08:00'),
            'end_time' => $this->getPost('end_time', '10:30'),
            'room' => trim($this->getPost('room', '')),
            'topic' => trim($this->getPost('topic', '')),
            'subtopic' => trim($this->getPost('subtopic', '')),
            'delivery_method' => $this->getPost('delivery_method', 'Tatap Muka'),
            'student_present' => $present,
            'student_total' => $total,
            'attendance_percentage' => $pct,
            'status' => $this->getPost('status', 'Terlaksana'),
            'lecturer_notes' => trim($this->getPost('lecturer_notes', ''))
        ]);

        $this->syncClassStats($classId);
        AuditService::log('UPDATE_MEETING', 'class_meetings', (string)$meetingId, null, ['class_id' => $classId]);
        $this->redirect('academic/class-detail?id=' . $classId, ['success' => 'Perubahan log pertemuan berhasil disimpan.']);
    }

    /**
     * Hapus Log Pertemuan
     */
    public function deleteMeeting(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $meetingId = (int)$this->getPost('meeting_id', 0);
        $classId = (int)$this->getPost('class_id', 0);

        $meetingModel = new ClassMeeting();
        $meetingModel->delete($meetingId);

        $this->syncClassStats($classId);
        AuditService::log('DELETE_MEETING', 'class_meetings', (string)$meetingId, null, ['class_id' => $classId]);
        $this->redirect('academic/class-detail?id=' . $classId, ['warning' => 'Log pertemuan telah dihapus.']);
    }

    /**
     * Hitung Ulang Total Realisasi Pertemuan dan Rata-rata Kehadiran Kelas
     */
    private function syncClassStats(int $classId): void {
        $meetingModel = new ClassMeeting();
        $summary = $meetingModel->getSummaryByClass($classId);
        $held = (int)($summary['held_count'] ?? 0);
        $avgAtt = round((float)($summary['avg_attendance'] ?? 0), 2);

        $classModel = new ClassModel();
        $classInfo = $classModel->find($classId);
        $planned = (int)($classInfo['total_planned_meetings'] ?? 16);

        // Otomatis cek problem flag jika realisasi < 50% atau rata presensi < 75%
        $isProblem = ($held < ($planned * 0.5) || ($held > 0 && $avgAtt < 75.0)) ? 1 : 0;
        $problemNotes = $classInfo['problem_notes'];
        if ($isProblem && empty($problemNotes)) {
            $problemNotes = 'Perkuliahan terdeteksi di bawah target minimum (Realisasi: ' . $held . '/' . $planned . ', Presensi: ' . $avgAtt . '%)';
        }

        $classModel->update($classId, [
            'total_held_meetings' => $held,
            'average_attendance'  => $avgAtt,
            'problem_flag'        => $isProblem,
            'problem_notes'       => $problemNotes
        ]);
    }

    /**
     * Dashboard Analitik & Monitoring Kesiapan RPS
     */
    public function rpsMonitoring(): void {
        $this->requireAuth();

        $courseModel = new Course();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterStatus = trim($this->getQuery('status', ''));
        $filterCaseMethod = $this->getQuery('case_method', '');

        $courses = $courseModel->allWithLecturerAndProgram($filterProgram ?: null);

        // Filter status / case method
        if (!empty($filterStatus)) {
            $courses = array_filter($courses, fn($c) => $c['rps_status'] === $filterStatus);
        }
        if ($filterCaseMethod !== '') {
            $courses = array_filter($courses, fn($c) => (int)($c['case_method_flag'] ?? 0) === (int)$filterCaseMethod);
        }

        $programs = $programModel->all();
        $summary = $courseModel->getRpsSummary();
        $prodiStats = $courseModel->getRpsByProdi();

        $this->render('academic/rps_monitoring', [
            'title'             => 'Dashboard Analitik & Monitoring Kesiapan RPS Dekanat',
            'courses'           => $courses,
            'programs'          => $programs,
            'filterProgram'     => $filterProgram,
            'filterStatus'      => $filterStatus,
            'filterCaseMethod'  => $filterCaseMethod,
            'summary'           => $summary,
            'prodiStats'        => $prodiStats
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

    /**
     * Ekspor Laporan Monitoring Pertemuan Perkuliahan (CSV / Cetak)
     */
    public function exportLecture(): void {
        $this->requireAuth();
        $format = $this->getQuery('format', 'csv');

        $classModel = new ClassModel();
        $ayModel = new AcademicYear();
        $activeAy = $ayModel->getActive();
        $classes = $classModel->allWithDetails($activeAy ? (int)$activeAy['id'] : null);

        AuditService::log('EXPORT_LECTURE', 'classes', null, null, ['format' => $format]);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=monitoring_pertemuan_perkuliahan_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Kelas', 'Dosen Pengampu', 'Program Studi', 'Realisasi Pertemuan', 'Target Pertemuan', 'Rata-rata Presensi (%)', 'Status Problem', 'Catatan Dekanat']);
            foreach ($classes as $c) {
                fputcsv($out, [
                    $c['course_code'],
                    $c['course_name'],
                    $c['sks'],
                    $c['class_name'],
                    $c['lecturer_name'],
                    $c['program_name'],
                    $c['total_held_meetings'],
                    $c['total_planned_meetings'],
                    $c['average_attendance'],
                    $c['problem_flag'] ? 'Bermasalah / Perlu Evaluasi' : 'Normal',
                    $c['problem_notes'] ?? '-'
                ]);
            }
            fclose($out);
            exit;
        }

        // Print preview
        $this->render('academic/print_lecture', [
            'title'    => 'Laporan Monitoring Tatap Muka & Presensi Perkuliahan',
            'classes'  => $classes,
            'activeAy' => $activeAy
        ], 'layouts/print');
    }

    /**
     * Ekspor Laporan Kesiapan RPS & Kurikulum OBE (CSV / Cetak)
     */
    public function exportRps(): void {
        $this->requireAuth();
        $format = $this->getQuery('format', 'csv');

        $courseModel = new Course();
        $courses = $courseModel->allWithLecturerAndProgram();
        $summary = $courseModel->getRpsSummary();

        AuditService::log('EXPORT_RPS', 'courses', null, null, ['format' => $format]);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=monitoring_kesiapan_rps_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Program Studi', 'Dosen Koordinator', 'Status RPS', 'Kesesuaian CPL', 'Metode Case Method (IKU-7)', 'Link Dokumen RPS', 'Catatan / Review Dekanat']);
            foreach ($courses as $c) {
                fputcsv($out, [
                    $c['code'],
                    $c['name'],
                    $c['sks'],
                    $c['semester'],
                    $c['program_name'],
                    $c['lecturer_name'] ?? '-',
                    $c['rps_status'],
                    $c['cpl_status'] ?? 'Terpenuhi',
                    !empty($c['case_method_flag']) ? 'Ya (Case Method)' : 'Reguler',
                    $c['rps_document_url'] ?? '-',
                    $c['rps_notes'] ?? '-'
                ]);
            }
            fclose($out);
            exit;
        }

        // Print preview
        $this->render('academic/print_rps', [
            'title'   => 'Laporan Audit Kesiapan RPS & Kurikulum OBE',
            'courses' => $courses,
            'summary' => $summary
        ], 'layouts/print');
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
        $cplStatus = trim($this->getPost('cpl_status', 'Memenuhi Standar OBE'));
        $caseMethod = (int)$this->getPost('case_method_flag', 0);
        $docUrl = trim($this->getPost('rps_document_url', ''));
        $notes = trim($this->getPost('notes', ''));
        $returnUrl = $this->getPost('return_url', 'academic/rps-monitoring');

        if ($courseId <= 0) {
            $this->redirect('academic/rps-monitoring', ['danger' => 'ID Mata Kuliah tidak valid.']);
        }

        $courseModel = new Course();
        $courseModel->update($courseId, [
            'rps_status'       => $status,
            'cpl_status'       => $cplStatus,
            'case_method_flag' => $caseMethod,
            'rps_document_url' => !empty($docUrl) ? $docUrl : null,
            'rps_updated_at'   => date('Y-m-d'),
            'rps_notes'        => $notes
        ]);

        AuditService::log('APPROVE_RPS', 'courses', (string)$courseId, null, [
            'status'      => $status,
            'cpl_status'  => $cplStatus,
            'case_method' => $caseMethod,
            'notes'       => $notes
        ]);

        $this->redirect($returnUrl, ['success' => "Status RPS dan validasi kurikulum berhasil diperbarui menjadi '{$status}'."]);
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
