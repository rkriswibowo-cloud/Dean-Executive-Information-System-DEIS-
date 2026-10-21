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
use App\Helpers\AuthHelper;
use App\Services\Obe\ObeSnapshotService;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository as ObeStudentRepository;
use App\Services\Obe\LecturerRepository as ObeLecturerRepository;

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

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterStatus = trim((string)$this->getQuery('status', ''));
        $filterCaseMethod = $this->getQuery('case_method', '');

        [$courses, $summary, $prodiStats, $programs, $obeAvailable] = $this->getNormalizedObeRpsData(
            $filterProgram ?: null,
            $filterStatus,
            $filterCaseMethod
        );

        $this->render('academic/rps_monitoring', [
            'title'             => 'Dashboard Analitik & Monitoring Kesiapan RPS Dekanat',
            'courses'           => $courses,
            'programs'          => $programs,
            'filterProgram'     => $filterProgram,
            'filterStatus'      => $filterStatus,
            'filterCaseMethod'  => $filterCaseMethod,
            'summary'           => $summary,
            'prodiStats'        => $prodiStats,
            'obeAvailable'      => $obeAvailable
        ]);
    }

    /**
     * Helper data mata kuliah realtime dari master OBE untuk monitoring & ekspor RPS
     * Mengembalikan data yang strictly sesuai master OBE, mengabaikan dummy data lokal non-OBE
     */
    private function getNormalizedObeRpsData(?int $filterProgram = null, string $filterStatus = '', string $filterCaseMethod = ''): array {
        $obeService = new \App\Services\ObeSyncService();
        $obeAvailable = $obeService->isObeAvailable();

        // 1. Program Studi OBE
        $programs = [];
        try {
            $studyProgramRepo = new \App\Services\Obe\StudyProgramRepository();
            $allObePrograms = $studyProgramRepo->all(false);
            $programs = array_map(function ($p) {
                return [
                    'id'     => (int)$p['obe_id'],
                    'name'   => $p['name'],
                    'degree' => $p['degree'] ?? 'S1',
                    'code'   => $p['code'] ?? ''
                ];
            }, $allObePrograms);
        } catch (\Throwable $e) {
            $programModel = new StudyProgram();
            $programs = $programModel->all();
        }

        // Resolusi filter program (jika dikirim ID DEIS lama, cocokkan ke OBE prodi ID)
        $obeProdiId = null;
        if ($filterProgram > 0) {
            $obeProdiId = $filterProgram;
            $matchingObe = array_filter($programs, fn($p) => (int)$p['id'] === $filterProgram);
            if (empty($matchingObe)) {
                try {
                    $db = \App\Core\Database::getConnection();
                    $mappedObeId = $db->query("SELECT obe_id FROM obe_entity_mappings WHERE entity_type='study_program' AND deis_id=" . (int)$filterProgram)->fetchColumn();
                    if ($mappedObeId) {
                        $obeProdiId = (int)$mappedObeId;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Fallback jika database OBE tidak terdeteksi
        if (!$obeAvailable) {
            $courseModel = new Course();
            $courses = $courseModel->allWithLecturerAndProgram($filterProgram ?: null, true);
            if (!empty($filterStatus)) {
                $courses = array_filter($courses, fn($c) => $c['rps_status'] === $filterStatus);
            }
            if ($filterCaseMethod !== '') {
                $courses = array_filter($courses, fn($c) => (int)($c['case_method_flag'] ?? 0) === (int)$filterCaseMethod);
            }
            return [
                array_values($courses),
                $courseModel->getRpsSummary(),
                $courseModel->getRpsByProdi(),
                $programs,
                false
            ];
        }

        // 2. Ambil data mentah langsung dari master OBE
        $rawCourses = $obeService->getRealtimeObeCourses();

        // 3. Ambil data validasi dekanat lokal jika ada
        $validations = [];
        try {
            $db = \App\Core\Database::getConnection();
            $valRows = $db->query("SELECT * FROM obe_rps_validations")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($valRows as $vr) {
                $validations[(int)$vr['obe_course_id']] = $vr;
            }
        } catch (\Throwable $e) {}

        // 4. Normalisasi data mata kuliah master OBE
        $allCourses = [];
        foreach ($rawCourses as $m) {
            $mkId = (int)$m['obe_mk_id'];
            $val = $validations[$mkId] ?? null;

            $lecturerName = $m['pjmk_nama'] ?? null;
            if (empty($lecturerName) && !empty($m['rps_dosen_pengampu'])) {
                $firstLine = trim(strtok((string)$m['rps_dosen_pengampu'], "\r\n"));
                $firstLine = preg_replace('/^\s*\d+\.\s*/u', '', $firstLine);
                $clean = trim((string)preg_replace('/\s*\(PJMK\).*$/iu', '', $firstLine));
                $lecturerName = $clean !== '' ? $clean : null;
            }

            $rpsStatus = $val['rps_status'] ?? $m['rps_status'];
            $caseMethod = isset($val['case_method_flag']) ? (int)$val['case_method_flag'] : (int)$m['is_case_method'];
            $cplStatus = $val['cpl_status'] ?? ($rpsStatus === 'Lengkap' ? 'Memenuhi Standar OBE' : ($rpsStatus === 'Revisi' ? 'Revisi Rubrik Penilaian' : 'Belum Lengkap'));
            $docUrl = !empty($val['rps_document_url']) ? $val['rps_document_url'] : (!empty($m['obe_rps_id']) ? "http://localhost/obe/rps/preview/{$mkId}" : null);
            $notes = $val['rps_notes'] ?? null;
            $updatedAt = $val['rps_updated_at'] ?? (!empty($m['rps_updated_at']) ? date('Y-m-d', strtotime($m['rps_updated_at'])) : null);

            $allCourses[] = [
                'id'                 => $mkId,
                'obe_mk_id'          => $mkId,
                'code'               => $m['code'],
                'name'               => $m['name'],
                'sks'                => (int)$m['sks'],
                'semester'           => (int)$m['semester'],
                'program_id'         => (int)$m['obe_prodi_id'],
                'obe_prodi_id'       => (int)$m['obe_prodi_id'],
                'program_name'       => $m['prodi_nama'],
                'prodi_jenjang'      => $m['prodi_jenjang'],
                'kurikulum_year'     => $m['kurikulum_year'],
                'kurikulum_nama'     => $m['kurikulum_nama'],
                'lecturer_name'      => $lecturerName,
                'rps_status'         => $rpsStatus,
                'cpl_status'         => $cplStatus,
                'case_method_flag'   => $caseMethod,
                'rps_document_url'   => $docUrl,
                'rps_updated_at'     => $updatedAt,
                'rps_notes'          => $notes,
            ];
        }

        // 5. Hitung Summary & Prodi Stats dari master OBE
        $summary = [
            'total_courses'     => count($allCourses),
            'complete_count'    => 0,
            'revision_count'    => 0,
            'incomplete_count'  => 0,
            'case_method_count' => 0
        ];

        $prodiMap = [];
        foreach ($programs as $p) {
            $prodiMap[$p['id']] = [
                'program_id'        => $p['id'],
                'program_name'      => $p['name'],
                'degree'            => $p['degree'],
                'total_courses'     => 0,
                'complete_count'    => 0,
                'revision_count'    => 0,
                'incomplete_count'  => 0,
                'case_method_count' => 0
            ];
        }

        foreach ($allCourses as $c) {
            if ($c['rps_status'] === 'Lengkap') $summary['complete_count']++;
            elseif ($c['rps_status'] === 'Revisi') $summary['revision_count']++;
            else $summary['incomplete_count']++;

            if (!empty($c['case_method_flag'])) $summary['case_method_count']++;

            $pid = $c['obe_prodi_id'];
            if (!isset($prodiMap[$pid])) {
                $prodiMap[$pid] = [
                    'program_id'        => $pid,
                    'program_name'      => $c['program_name'],
                    'degree'            => $c['prodi_jenjang'] ?? 'S1',
                    'total_courses'     => 0,
                    'complete_count'    => 0,
                    'revision_count'    => 0,
                    'incomplete_count'  => 0,
                    'case_method_count' => 0
                ];
            }
            $prodiMap[$pid]['total_courses']++;
            if ($c['rps_status'] === 'Lengkap') $prodiMap[$pid]['complete_count']++;
            elseif ($c['rps_status'] === 'Revisi') $prodiMap[$pid]['revision_count']++;
            else $prodiMap[$pid]['incomplete_count']++;
            if (!empty($c['case_method_flag'])) $prodiMap[$pid]['case_method_count']++;
        }
        $prodiStats = array_values($prodiMap);

        // 6. Filter daftar mata kuliah sesuai query
        $courses = $allCourses;
        if ($obeProdiId) {
            $courses = array_filter($courses, fn($c) => (int)$c['obe_prodi_id'] === $obeProdiId);
        }
        if (!empty($filterStatus)) {
            $courses = array_filter($courses, fn($c) => $c['rps_status'] === $filterStatus);
        }
        if ($filterCaseMethod !== '') {
            $courses = array_filter($courses, fn($c) => (int)($c['case_method_flag'] ?? 0) === (int)$filterCaseMethod);
        }

        return [
            array_values($courses),
            $summary,
            $prodiStats,
            $programs,
            true
        ];
    }

    /**
     * Tampilan Realtime Data Langsung dari Sistem OBE (Opsi 1)
     */
    public function obeLive(): void {
        $this->requireAuth();

        $obeService = new \App\Services\ObeSyncService();
        if (!$obeService->isObeAvailable()) {
            $this->redirect('academic/rps-monitoring', ['danger' => 'Database OBE (obe_cpl) tidak terdeteksi di server lokal.']);
        }

        $filterProdi = (int)$this->getQuery('prodi_id', 0);
        $obeCourses = $obeService->getRealtimeObeCourses($filterProdi ?: null);

        $programModel = new StudyProgram();
        $programs = $programModel->all();

        $this->render('academic/obe_live', [
            'title'       => 'Data Realtime Mata Kuliah & RPS Langsung dari Sistem OBE',
            'obeCourses'  => $obeCourses,
            'programs'    => $programs,
            'filterProdi' => $filterProdi,
            'snapshots'   => (new ObeSnapshotService())->latest(8)
        ]);
    }

    /**
     * Endpoint kompatibilitas lama. Penyalinan master sudah dinonaktifkan.
     */
    public function syncObe(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $returnUrl = $this->getPost('return_url', 'academic/obe-live');
        $this->redirect($returnUrl, ['warning' => 'Penyalinan master telah dinonaktifkan. DEIS sekarang membaca data OBE secara realtime.']);
    }

    public function captureObeSnapshot(): void {
        $this->requireAuth();
        $this->requireCsrf();
        try {
            $snapshot = (new ObeSnapshotService())->captureAcademicMaster(AuthHelper::id());
            AuditService::log('CAPTURE_OBE_SNAPSHOT', 'obe_snapshots', (string)$snapshot['id'], null, $snapshot);
            $this->redirect('academic/obe-live', [
                'success' => "Snapshot #{$snapshot['id']} berhasil disimpan ({$snapshot['record_count']} record)."
            ]);
        } catch (\Throwable $e) {
            $this->redirect('academic/obe-live', ['danger' => 'Snapshot gagal: ' . $e->getMessage()]);
        }
    }

    public function courses(): void {
        $this->requireAuth();
        $this->redirect('master/obe?section=courses');
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
            fputcsv($out, ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Kelas', 'Dosen Pengampu', 'Program Studi', 'Realisasi Pertemuan', 'Target Pertemuan', 'Rata-rata Presensi (%)', 'Status Problem', 'Catatan Dekanat'], ',', '"', "\\");
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
                ], ',', '"', "\\");
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

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterStatus = trim((string)$this->getQuery('status', ''));
        $filterCaseMethod = $this->getQuery('case_method', '');

        [$courses, $summary] = $this->getNormalizedObeRpsData(
            $filterProgram ?: null,
            $filterStatus,
            $filterCaseMethod
        );

        AuditService::log('EXPORT_RPS', 'obe_courses', null, null, ['format' => $format]);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=monitoring_kesiapan_rps_obe_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Program Studi', 'Dosen Koordinator', 'Status RPS', 'Kesesuaian CPL', 'Metode Case Method (IKU-7)', 'Link Dokumen RPS', 'Catatan / Review Dekanat'], ',', '"', "\\");
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
                ], ',', '"', "\\");
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
        $obeError = null;
        try {
            $obe = new ObeReadConnection();
            $students = (new ObeStudentRepository($obe))->all();
            $lecturers = (new ObeLecturerRepository($obe))->all();
            $studentIndex = array_column($students, null, 'obe_id');
            $lecturerIndex = array_column($lecturers, null, 'obe_id');
            $guidances = $guidanceModel->allObeCases($filterType ?: null);
            foreach ($guidances as &$guidance) {
                $guidance['students'] = array_values(array_filter(array_map(static fn(int $id) => $studentIndex[$id] ?? null, $guidance['student_ids'])));
                $guidance['lecturers'] = array_values(array_filter(array_map(static fn(int $id) => $lecturerIndex[$id] ?? null, $guidance['lecturer_ids'])));
            }
            unset($guidance);
        } catch (\Throwable $e) {
            $guidances = $students = $lecturers = [];
            $obeError = $e->getMessage();
        }

        $this->render('academic/guidance', [
            'title'      => 'Bimbingan Skripsi, KKN & PKL',
            'guidances'  => $guidances,
            'students'   => $students,
            'lecturers'  => $lecturers,
            'filterType' => $filterType,
            'obeError'   => $obeError
        ]);
    }

    public function createGuidance(): void {
        $this->requireAuth();
        $this->requireCsrf();
        [$data, $students, $lecturers, $error] = $this->validatedGuidancePayload();
        if ($error) $this->redirect('academic/guidance', ['error' => $error]);
        $id = (new Guidance())->createObeCase($data, $students, $lecturers);
        AuditService::log('CREATE_GUIDANCE', 'guidance_cases', (string)$id, null, ['type' => $data['type'], 'students' => $students, 'lecturers' => $lecturers]);
        $this->redirect('academic/guidance', ['success' => 'Data bimbingan berhasil ditambahkan.']);
    }

    public function guidanceDetail(): void {
        $this->requireAuth();
        $id = (int)$this->getQuery('id', 0);
        $model = new Guidance();
        $case = $model->findObeCase($id);
        if (!$case) $this->redirect('academic/guidance', ['error' => 'Data bimbingan tidak ditemukan.']);
        try {
            $obe = new ObeReadConnection();
            $studentIndex = array_column((new ObeStudentRepository($obe))->all(), null, 'obe_id');
            $lecturerIndex = array_column((new ObeLecturerRepository($obe))->all(), null, 'obe_id');
            $case['students'] = array_values(array_filter(array_map(static fn(int $studentId) => $studentIndex[$studentId] ?? null, $case['student_ids'])));
            $case['lecturers'] = array_values(array_filter(array_map(static fn(int $lecturerId) => $lecturerIndex[$lecturerId] ?? null, $case['lecturer_ids'])));
            $sessions = $model->sessions($id);
            $counts = array_fill_keys($case['student_ids'], 0);
            foreach ($sessions as &$session) {
                $session['students'] = array_values(array_filter(array_map(static fn(int $studentId) => $studentIndex[$studentId] ?? null, $session['student_ids'])));
                foreach ($session['student_ids'] as $studentId) if (isset($counts[$studentId])) $counts[$studentId]++;
            }
            unset($session);
        } catch (\Throwable $e) {
            $this->redirect('academic/guidance', ['error' => 'Data OBE tidak dapat dibaca: ' . $e->getMessage()]);
        }
        $this->render('academic/guidance_detail', ['title'=>'Detail Bimbingan', 'case'=>$case, 'sessions'=>$sessions, 'guidanceCounts'=>$counts]);
    }

    public function createGuidanceSession(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $caseId = (int)$this->getPost('guidance_case_id', 0);
        $model = new Guidance();
        $case = $model->findObeCase($caseId);
        if (!$case) $this->redirect('academic/guidance', ['error'=>'Aktivitas bimbingan tidak ditemukan.']);
        $studentIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['student_ids'] ?? [])))));
        if (!$studentIds || array_diff($studentIds, $case['student_ids'])) {
            $this->redirect('academic/guidance/detail?id='.$caseId, ['error'=>'Pilih minimal satu mahasiswa anggota aktivitas ini.']);
        }
        $date = trim($this->getPost('guidance_date', ''));
        $topic = trim($this->getPost('topic', ''));
        if ($date === '' || $topic === '') $this->redirect('academic/guidance/detail?id='.$caseId, ['error'=>'Tanggal dan topik bimbingan wajib diisi.']);
        $progressRaw = trim((string)$this->getPost('progress_after', ''));
        $progress = $progressRaw === '' ? null : min(100, max(0, (float)$progressRaw));
        $sessionId = $model->createSession($caseId, ['guidance_date'=>$date,'topic'=>$topic,'notes'=>trim($this->getPost('notes','')) ?: null,'progress_after'=>$progress], $studentIds);
        AuditService::log('CREATE_GUIDANCE_SESSION', 'guidance_sessions', (string)$sessionId, null, ['case_id'=>$caseId,'students'=>$studentIds]);
        $this->redirect('academic/guidance/detail?id='.$caseId, ['success'=>'Sesi bimbingan berhasil dicatat.']);
    }

    public function deleteGuidanceSession(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $caseId = (int)$this->getPost('guidance_case_id', 0);
        $sessionId = (int)$this->getPost('session_id', 0);
        (new Guidance())->deleteSession($sessionId, $caseId);
        AuditService::log('DELETE_GUIDANCE_SESSION', 'guidance_sessions', (string)$sessionId, null, ['case_id'=>$caseId]);
        $this->redirect('academic/guidance/detail?id='.$caseId, ['success'=>'Riwayat sesi bimbingan berhasil dihapus.']);
    }

    public function updateGuidance(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $id = (int)$this->getPost('guidance_id', 0);
        if ($id <= 0) $this->redirect('academic/guidance', ['error' => 'ID bimbingan tidak valid.']);
        [$data, $students, $lecturers, $error] = $this->validatedGuidancePayload();
        if ($error) $this->redirect('academic/guidance', ['error' => $error]);
        (new Guidance())->updateObeCase($id, $data, $students, $lecturers);
        AuditService::log('UPDATE_GUIDANCE', 'guidance_cases', (string)$id, null, ['type' => $data['type'], 'students' => $students, 'lecturers' => $lecturers]);
        $this->redirect('academic/guidance', ['success' => 'Data bimbingan berhasil diperbarui.']);
    }

    public function deleteGuidance(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $id = (int)$this->getPost('guidance_id', 0);
        if ($id <= 0) $this->redirect('academic/guidance', ['error' => 'ID bimbingan tidak valid.']);
        (new Guidance())->deleteObeCase($id);
        AuditService::log('DELETE_GUIDANCE', 'guidance_cases', (string)$id);
        $this->redirect('academic/guidance', ['success' => 'Data bimbingan berhasil dihapus.']);
    }

    private function validatedGuidancePayload(): array {
        $type = trim($this->getPost('type', ''));
        $studentIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['student_ids'] ?? [])))));
        $lecturerIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['lecturer_ids'] ?? [])))));
        if (!in_array($type, ['Skripsi', 'KKN', 'PKL'], true)) return [[], [], [], 'Jenis bimbingan tidak valid.'];
        if ($type === 'Skripsi' && count($studentIds) !== 1) return [[], [], [], 'Skripsi harus memiliki tepat 1 mahasiswa.'];
        if ($type === 'Skripsi' && count($lecturerIds) !== 2) return [[], [], [], 'Skripsi harus memiliki tepat 2 dosen pembimbing.'];
        if (in_array($type, ['KKN', 'PKL'], true) && count($studentIds) < 1) return [[], [], [], "{$type} harus memiliki minimal 1 mahasiswa."];
        if (count($lecturerIds) < 1 || count($lecturerIds) > 2) return [[], [], [], 'Pilih 1 sampai 2 dosen pembimbing.'];
        try {
            $obe = new ObeReadConnection();
            $validStudents = array_column((new ObeStudentRepository($obe))->all(), 'obe_id');
            $validLecturers = array_column((new ObeLecturerRepository($obe))->all(), 'obe_id');
            if (array_diff($studentIds, $validStudents)) return [[], [], [], 'Terdapat mahasiswa yang tidak valid di OBE.'];
            if (array_diff($lecturerIds, $validLecturers)) return [[], [], [], 'Terdapat dosen yang tidak valid di OBE.'];
        } catch (\Throwable $e) {
            return [[], [], [], 'Data OBE tidak dapat diverifikasi: ' . $e->getMessage()];
        }
        $title = trim($this->getPost('title', ''));
        if ($title === '') return [[], [], [], 'Judul atau nama kegiatan wajib diisi.'];
        $date = trim($this->getPost('last_guidance_date', ''));
        return [[
            'type' => $type, 'title' => $title,
            'progress' => min(100, max(0, (float)$this->getPost('progress_percentage', 0))),
            'status' => in_array($this->getPost('status', ''), ['Aktif','Selesai','Bermasalah','Terlambat'], true) ? $this->getPost('status') : 'Aktif',
            'last_date' => $date !== '' ? $date : null,
            'notes' => trim($this->getPost('notes', '')) ?: null,
        ], $studentIds, $lecturerIds, null];
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

        // Ambil data mata kuliah OBE secara realtime untuk pemilihan di modal tambah praktikum
        $obeCourses = [];
        $obeCurricula = [];
        try {
            $obeService = new \App\Services\Obe\ObeReadConnection();
            $obeRows = $obeService->fetchAll("
                SELECT 
                    m.id as obe_mk_id,
                    m.kode as course_code,
                    m.nama as course_name,
                    m.sks_teori,
                    m.sks_praktek,
                    (m.sks_teori + m.sks_praktek) as sks_total,
                    m.semester,
                    m.sifat,
                    k.id as kurikulum_id,
                    k.nama as kurikulum_nama,
                    p.id as obe_prodi_id,
                    p.nama as prodi_nama,
                    r.dosen_pengampu as rps_dosen,
                    u.nama_lengkap as pjmk_nama
                FROM mata_kuliah m
                JOIN kurikulum k ON m.kurikulum_id = k.id
                JOIN program_studi p ON k.prodi_id = p.id
                LEFT JOIN rps r ON r.mata_kuliah_id = m.id
                LEFT JOIN mata_kuliah_pengampu pm ON pm.mata_kuliah_id = m.id AND pm.status = 'ACTIVE'
                LEFT JOIN users u ON u.id = pm.pjmk_user_id
                WHERE m.status = 'ACTIVE'
                ORDER BY p.id ASC, k.id ASC, m.semester ASC, m.kode ASC
            ");

            // Mapping prodi OBE -> DEIS prodi ID
            $db = \App\Core\Database::getConnection();
            $mappings = $db->query("SELECT obe_id, deis_id FROM obe_entity_mappings WHERE entity_type='study_program' AND match_status='MAPPED'")->fetchAll(\PDO::FETCH_KEY_PAIR);

            foreach ($obeRows as $row) {
                $deisProdiId = $mappings[(int)$row['obe_prodi_id']] ?? null;
                // Fallback pencocokan nama prodi jika mapping belum ada
                if (!$deisProdiId) {
                    foreach ($programs as $prog) {
                        if (strcasecmp($prog['name'], $row['prodi_nama']) === 0) {
                            $deisProdiId = (int)$prog['id'];
                            break;
                        }
                    }
                }

                $lecturer = trim((string)($row['pjmk_nama'] ?? ''));
                if ($lecturer === '') {
                    $rpsDosen = trim(strtok((string)($row['rps_dosen'] ?? ''), "\r\n"));
                    $rpsDosen = preg_replace('/^\s*\d+\.\s*/u', '', $rpsDosen);
                    $lecturer = trim((string)preg_replace('/\s*\(PJMK\).*$/iu', '', $rpsDosen));
                }

                $courseItem = [
                    'obe_mk_id'       => (int)$row['obe_mk_id'],
                    'course_code'     => $row['course_code'],
                    'course_name'     => $row['course_name'],
                    'sks_teori'       => (int)$row['sks_teori'],
                    'sks_praktek'     => (int)$row['sks_praktek'],
                    'sks_total'       => (int)$row['sks_total'],
                    'semester'        => (int)$row['semester'],
                    'kurikulum_id'    => (int)$row['kurikulum_id'],
                    'kurikulum_nama'  => $row['kurikulum_nama'],
                    'obe_prodi_id'    => (int)$row['obe_prodi_id'],
                    'deis_prodi_id'   => $deisProdiId,
                    'prodi_nama'      => $row['prodi_nama'],
                    'lecturer_name'   => $lecturer
                ];

                $obeCourses[] = $courseItem;
                if ($deisProdiId) {
                    $obeCurricula[$deisProdiId][$row['kurikulum_id']] = $row['kurikulum_nama'];
                }
            }
        } catch (\Throwable $e) {
            $obeCourses = [];
            $obeCurricula = [];
        }

        $this->render('academic/practicum', [
            'title'          => 'Cek & Verifikasi Modul Praktikum Laboratorium',
            'modules'        => $modules,
            'programs'       => $programs,
            'prodiSummary'   => $prodiSummary,
            'prodiTotals'    => $prodiTotals,
            'stats'          => $stats,
            'filterProgram'  => $filterProgram,
            'filterSemester' => $filterSemester,
            'filterStatus'   => $filterStatus,
            'obeCourses'     => $obeCourses,
            'obeCurricula'   => $obeCurricula
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
            'academic_year_id'  => (int)(new AcademicYear())->getActive()['id'],
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

        // Simpan ke obe_rps_validations di database DEIS
        try {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO obe_rps_validations 
                    (obe_course_id, rps_status, cpl_status, case_method_flag, rps_document_url, rps_updated_at, rps_notes, validated_by)
                VALUES 
                    (:cid, :status, :cpl_status, :case_method, :doc_url, :updated_at, :notes, :user_id)
                ON DUPLICATE KEY UPDATE
                    rps_status = VALUES(rps_status),
                    cpl_status = VALUES(cpl_status),
                    case_method_flag = VALUES(case_method_flag),
                    rps_document_url = VALUES(rps_document_url),
                    rps_updated_at = VALUES(rps_updated_at),
                    rps_notes = VALUES(rps_notes),
                    validated_by = VALUES(validated_by),
                    updated_at = NOW()
            ");
            $stmt->execute([
                'cid'         => $courseId,
                'status'      => $status,
                'cpl_status'  => $cplStatus,
                'case_method' => $caseMethod,
                'doc_url'     => !empty($docUrl) ? $docUrl : null,
                'updated_at'  => date('Y-m-d'),
                'notes'       => $notes,
                'user_id'     => AuthHelper::id()
            ]);
        } catch (\Throwable $e) {}

        // Sinkronkan ke tabel courses lokal jika ID cocok untuk kompatibilitas modul lama
        try {
            $courseModel = new Course();
            if ($courseModel->find($courseId)) {
                $courseModel->update($courseId, [
                    'rps_status'       => $status,
                    'cpl_status'       => $cplStatus,
                    'case_method_flag' => $caseMethod,
                    'rps_document_url' => !empty($docUrl) ? $docUrl : null,
                    'rps_updated_at'   => date('Y-m-d'),
                    'rps_notes'        => $notes
                ]);
            }
        } catch (\Throwable $e) {}

        AuditService::log('APPROVE_RPS', 'obe_courses', (string)$courseId, null, [
            'status'      => $status,
            'cpl_status'  => $cplStatus,
            'case_method' => $caseMethod,
            'notes'       => $notes
        ]);

        $this->redirect($returnUrl, ['success' => "Status RPS dan validasi kurikulum OBE berhasil diperbarui menjadi '{$status}'."]);
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
