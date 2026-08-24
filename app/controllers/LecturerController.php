<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lecturer;
use App\Models\StudyProgram;

class LecturerController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $lecturerModel = new Lecturer();
        $programModel = new StudyProgram();

        $filterProgram = (int)$this->getQuery('program_id', 0);
        $filterBkd = $this->getQuery('bkd_status', '');

        $lecturers = $lecturerModel->allWithProgram($filterProgram ?: null);
        if ($filterBkd) {
            $lecturers = array_filter($lecturers, fn($l) => $l['bkd_status'] === $filterBkd);
        }

        $programs = $programModel->all();

        // Statistics
        $totalLecturers = count($lecturers);
        $s3Count = count(array_filter($lecturers, fn($l) => $l['education_level'] === 'S3'));
        $certifiedCount = count(array_filter($lecturers, fn($l) => $l['certification_status'] === 'Tersertifikasi'));
        $bkdMetCount = count(array_filter($lecturers, fn($l) => $l['bkd_status'] === 'Memenuhi'));
        $totalPubs = array_sum(array_column($lecturers, 'publication_count'));
        $totalPkm = array_sum(array_column($lecturers, 'pkm_count'));

        $this->render('lecturers/index', [
            'title'          => 'SDM & Kinerja Dosen',
            'lecturers'      => $lecturers,
            'programs'       => $programs,
            'filterProgram'  => $filterProgram,
            'filterBkd'      => $filterBkd,
            'totalLecturers' => $totalLecturers,
            's3Count'        => $s3Count,
            'certifiedCount' => $certifiedCount,
            'bkdMetCount'    => $bkdMetCount,
            'totalPubs'      => $totalPubs,
            'totalPkm'       => $totalPkm
        ]);
    }

    public function detail(): void {
        $this->requireAuth();
        $id = (int)$this->getQuery('id', 0);
        $lecturerModel = new Lecturer();
        $lecturer = $lecturerModel->find($id);

        if (!$lecturer) {
            $this->redirect('lecturers', ['error' => 'Data dosen tidak ditemukan.']);
        }

        $programModel = new StudyProgram();
        $program = $programModel->find($lecturer['study_program_id']);

        $this->render('lecturers/detail', [
            'title'    => 'Profil Kinerja Dosen - ' . $lecturer['name'],
            'lecturer' => $lecturer,
            'program'  => $program
        ]);
    }

    public function kpi(): void {
        $this->requireAuth();

        $lecturerModel = new Lecturer();
        $rankings = $lecturerModel->getKpiRanking();

        $this->render('lecturers/kpi', [
            'title'    => 'KPI & Ranking Kinerja Tri Dharma Dosen',
            'rankings' => $rankings
        ]);
    }

    public function create(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $name = trim($this->getPost('name', ''));
        $nidn = trim($this->getPost('nidn', ''));
        $nip = trim($this->getPost('nip', ''));
        $email = trim($this->getPost('email', ''));
        $phone = trim($this->getPost('phone', ''));
        $studyProgramId = (int)$this->getPost('study_program_id', 1);
        $academicRank = trim($this->getPost('academic_rank', 'Tenaga Pengajar'));
        $educationLevel = trim($this->getPost('education_level', 'S2'));
        $certificationStatus = trim($this->getPost('certification_status', 'Belum'));
        $bkdStatus = trim($this->getPost('bkd_status', 'Memenuhi'));
        $teachingLoadSks = (int)$this->getPost('teaching_load_sks', 12);
        $attendancePercentage = (float)$this->getPost('attendance_percentage', 90.0);
        $sintaScore = (int)$this->getPost('sinta_score', 0);
        $scopusHIndex = (int)$this->getPost('scopus_h_index', 0);
        $publicationCount = (int)$this->getPost('publication_count', 0);
        $pkmCount = (int)$this->getPost('pkm_count', 0);
        $hkiCount = (int)$this->getPost('hki_count', 0);
        $booksCount = (int)$this->getPost('books_count', 0);
        $status = trim($this->getPost('status', 'Aktif'));

        if (empty($name) || empty($nidn)) {
            $this->redirect('lecturers', ['error' => 'Nama lengkap dan NIDN wajib diisi.']);
        }

        $lecturerModel = new Lecturer();
        $newId = $lecturerModel->create([
            'study_program_id'      => $studyProgramId,
            'nidn'                  => $nidn,
            'nip'                   => $nip,
            'name'                  => $name,
            'email'                 => $email,
            'phone'                 => $phone,
            'academic_rank'         => $academicRank,
            'education_level'       => $educationLevel,
            'certification_status'  => $certificationStatus,
            'bkd_status'            => $bkdStatus,
            'teaching_load_sks'     => $teachingLoadSks,
            'attendance_percentage' => $attendancePercentage,
            'sinta_score'           => $sintaScore,
            'scopus_h_index'        => $scopusHIndex,
            'publication_count'     => $publicationCount,
            'pkm_count'             => $pkmCount,
            'hki_count'             => $hkiCount,
            'books_count'           => $booksCount,
            'status'                => $status
        ]);

        // Increment lecturer_count on study_programs
        $lecturerModel->rawQuery("UPDATE study_programs SET lecturer_count = lecturer_count + 1 WHERE id = :pid", ['pid' => $studyProgramId]);

        \App\Services\AuditService::log('CREATE', 'lecturers', (string)$newId, null, ['name' => $name, 'nidn' => $nidn]);
        $this->redirect('lecturers', ['success' => 'Data dosen baru berhasil ditambahkan.']);
    }

    public function update(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        $name = trim($this->getPost('name', ''));
        $nidn = trim($this->getPost('nidn', ''));
        $nip = trim($this->getPost('nip', ''));
        $email = trim($this->getPost('email', ''));
        $phone = trim($this->getPost('phone', ''));
        $studyProgramId = (int)$this->getPost('study_program_id', 1);
        $academicRank = trim($this->getPost('academic_rank', 'Tenaga Pengajar'));
        $educationLevel = trim($this->getPost('education_level', 'S2'));
        $certificationStatus = trim($this->getPost('certification_status', 'Belum'));
        $bkdStatus = trim($this->getPost('bkd_status', 'Memenuhi'));
        $teachingLoadSks = (int)$this->getPost('teaching_load_sks', 12);
        $attendancePercentage = (float)$this->getPost('attendance_percentage', 90.0);
        $sintaScore = (int)$this->getPost('sinta_score', 0);
        $scopusHIndex = (int)$this->getPost('scopus_h_index', 0);
        $publicationCount = (int)$this->getPost('publication_count', 0);
        $pkmCount = (int)$this->getPost('pkm_count', 0);
        $hkiCount = (int)$this->getPost('hki_count', 0);
        $booksCount = (int)$this->getPost('books_count', 0);
        $status = trim($this->getPost('status', 'Aktif'));

        if (!$id || empty($name) || empty($nidn)) {
            $this->redirect('lecturers', ['error' => 'Data dosen tidak valid.']);
        }

        $lecturerModel = new Lecturer();
        $lecturerModel->update($id, [
            'study_program_id'      => $studyProgramId,
            'nidn'                  => $nidn,
            'nip'                   => $nip,
            'name'                  => $name,
            'email'                 => $email,
            'phone'                 => $phone,
            'academic_rank'         => $academicRank,
            'education_level'       => $educationLevel,
            'certification_status'  => $certificationStatus,
            'bkd_status'            => $bkdStatus,
            'teaching_load_sks'     => $teachingLoadSks,
            'attendance_percentage' => $attendancePercentage,
            'sinta_score'           => $sintaScore,
            'scopus_h_index'        => $scopusHIndex,
            'publication_count'     => $publicationCount,
            'pkm_count'             => $pkmCount,
            'hki_count'             => $hkiCount,
            'books_count'           => $booksCount,
            'status'                => $status
        ]);

        \App\Services\AuditService::log('UPDATE', 'lecturers', (string)$id, null, ['name' => $name, 'nidn' => $nidn]);
        $this->redirect('lecturers', ['success' => 'Data dosen berhasil diperbarui.']);
    }

    public function delete(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id) {
            $lecturerModel = new Lecturer();
            $lecturer = $lecturerModel->find($id);
            if ($lecturer) {
                $lecturerModel->delete($id);
                $lecturerModel->rawQuery("UPDATE study_programs SET lecturer_count = GREATEST(0, lecturer_count - 1) WHERE id = :pid", ['pid' => $lecturer['study_program_id']]);
                \App\Services\AuditService::log('DELETE', 'lecturers', (string)$id, null, ['name' => $lecturer['name'] ?? '']);
                $this->redirect('lecturers', ['success' => 'Data dosen berhasil dihapus.']);
            }
        }
        $this->redirect('lecturers', ['error' => 'Gagal menghapus data dosen.']);
    }

    public function actionBkd(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $lecturerId = (int)$this->getPost('lecturer_id', 0);
        $bkdStatus = $this->getPost('bkd_status', 'Memenuhi');
        $notes = trim($this->getPost('action_notes', ''));

        if ($lecturerId <= 0) {
            $this->redirect('lecturers', ['danger' => 'ID Dosen tidak valid.']);
        }

        $lecturerModel = new Lecturer();
        $lecturer = $lecturerModel->find($lecturerId);

        $lecturerModel->update($lecturerId, [
            'bkd_status' => $bkdStatus
        ]);

        \App\Services\AuditService::log('EVAL_BKD_DEKAN', 'lecturers', (string)$lecturerId, $lecturer, [
            'bkd_status'   => $bkdStatus,
            'action_notes' => $notes
        ]);

        $this->redirect('lecturers', ['success' => "Keputusan status BKD Dosen ('{$bkdStatus}') berhasil disahkan oleh Dekanat."]);
    }

    public function actionKpi(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $lecturerId = (int)$this->getPost('lecturer_id', 0);
        $decisionType = $this->getPost('decision_type', 'Reward'); // 'Reward', 'Incentive', 'Guidance'
        $notes = trim($this->getPost('action_notes', ''));

        if ($lecturerId <= 0) {
            $this->redirect('lecturers/kpi', ['danger' => 'ID Dosen tidak valid.']);
        }

        \App\Services\AuditService::log('KPI_DEAN_DECISION', 'lecturers', (string)$lecturerId, null, [
            'decision_type' => $decisionType,
            'action_notes'  => $notes
        ]);

        $this->redirect('lecturers/kpi', ['success' => "Aksi Penetapan Insentif / Rekomendasi Dekanat ({$decisionType}) berhasil dicatat."]);
    }
}
