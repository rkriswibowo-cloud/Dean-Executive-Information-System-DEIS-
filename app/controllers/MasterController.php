<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\AcademicYear;
use App\Services\AuditService;

class MasterController extends Controller {
    public function faculties(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'dekan', 'developer']);

        $facultyModel = new Faculty();
        $faculties = $facultyModel->allWithStats();
        $activeFaculty = $facultyModel->find(1);

        $this->render('master/faculties', [
            'title'         => 'Data Master Fakultas',
            'faculties'     => $faculties,
            'activeFaculty' => $activeFaculty
        ]);
    }

    public function createFaculty(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'dekan', 'developer']);
        $this->requireCsrf();

        $code = strtoupper(trim($this->getPost('code', '')));
        $name = trim($this->getPost('name', ''));
        $deanName = trim($this->getPost('dean_name', ''));
        $vision = trim($this->getPost('vision', ''));
        $mission = trim($this->getPost('mission', ''));

        if (empty($code) || empty($name)) {
            $this->redirect('master/faculties', ['error' => 'Kode dan nama fakultas wajib diisi.']);
        }

        $facultyModel = new Faculty();
        $newId = $facultyModel->create([
            'code'      => $code,
            'name'      => $name,
            'dean_name' => $deanName,
            'vision'    => $vision,
            'mission'   => $mission
        ]);

        AuditService::log('CREATE', 'faculties', (string)$newId, null, ['code' => $code, 'name' => $name]);
        $this->redirect('master/faculties', ['success' => 'Fakultas baru berhasil ditambahkan.']);
    }

    public function updateFaculty(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'dekan', 'developer']);
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 1);
        $code = strtoupper(trim($this->getPost('code', '')));
        $name = trim($this->getPost('name', ''));
        $deanName = trim($this->getPost('dean_name', ''));
        $vision = trim($this->getPost('vision', ''));
        $mission = trim($this->getPost('mission', ''));

        if (!$id || empty($name)) {
            $this->redirect('master/faculties', ['error' => 'Data fakultas tidak valid.']);
        }

        $facultyModel = new Faculty();
        $facultyModel->update($id, [
            'code'      => $code,
            'name'      => $name,
            'dean_name' => $deanName,
            'vision'    => $vision,
            'mission'   => $mission
        ]);

        $db = $facultyModel->getDb();
        if ($id === 1) {
            // Sync app_settings
            $db->prepare("UPDATE app_settings SET setting_value = :name WHERE setting_key = 'dean_name'")->execute(['name' => $deanName]);
            $db->prepare("UPDATE app_settings SET setting_value = :name WHERE setting_key = 'faculty_name'")->execute(['name' => $name]);

            // Sync user Dekan and lecturer record
            $db->prepare("UPDATE users SET name = :name WHERE role_id = 2 OR username = 'dekan'")->execute(['name' => $deanName]);
            $db->prepare("UPDATE lecturers SET name = :name WHERE user_id = 2 OR nidn = '0012057801'")->execute(['name' => $deanName]);

            // If current user is Dekan, update active session
            if (isset($_SESSION['user_data']) && ($_SESSION['user_data']['role_slug'] === 'dekan' || $_SESSION['user_data']['role_id'] == 2)) {
                $_SESSION['user_data']['name'] = $deanName;
            }
        }

        AuditService::log('UPDATE', 'faculties', (string)$id, null, ['name' => $name, 'dean_name' => $deanName]);
        $this->redirect('master/faculties', ['success' => 'Data profil fakultas berhasil diperbarui dan disinkronkan ke seluruh sistem.']);
    }

    public function deleteFaculty(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id === 1) {
            $this->redirect('master/faculties', ['error' => 'Fakultas utama (ID: 1) tidak dapat dihapus.']);
        }

        if ($id > 1) {
            $facultyModel = new Faculty();
            $faculty = $facultyModel->find($id);
            $facultyModel->delete($id);
            AuditService::log('DELETE', 'faculties', (string)$id, null, ['name' => $faculty['name'] ?? '']);
            $this->redirect('master/faculties', ['success' => 'Fakultas berhasil dihapus.']);
        }
        $this->redirect('master/faculties', ['error' => 'Gagal menghapus fakultas.']);
    }

    public function studyPrograms(): void {
        $this->requireAuth();
        $programModel = new StudyProgram();
        $programs = $programModel->allWithFaculty();

        $this->render('master/study_programs', [
            'title'    => 'Data Master Program Studi',
            'programs' => $programs
        ]);
    }

    public function createStudyProgram(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $code = trim($this->getPost('code', ''));
        $name = trim($this->getPost('name', ''));
        $degree = trim($this->getPost('degree', 'S1'));
        $headName = trim($this->getPost('head_name', ''));
        $accreditationStatus = trim($this->getPost('accreditation_status', 'Baik'));
        $accreditationScore = (int)$this->getPost('accreditation_score', 300);
        $targetRetention = (float)$this->getPost('target_retention', 85.0);
        $studentCount = (int)$this->getPost('student_count', 0);
        $lecturerCount = (int)$this->getPost('lecturer_count', 0);

        if (empty($code) || empty($name)) {
            $this->redirect('master/study-programs', ['error' => 'Kode dan nama program studi wajib diisi.']);
        }

        $programModel = new StudyProgram();
        $newId = $programModel->create([
            'faculty_id'           => 1,
            'code'                 => $code,
            'name'                 => $name,
            'degree'               => $degree,
            'head_name'            => $headName,
            'accreditation_status' => $accreditationStatus,
            'accreditation_score'  => $accreditationScore,
            'target_retention'     => $targetRetention,
            'student_count'        => $studentCount,
            'lecturer_count'       => $lecturerCount
        ]);

        AuditService::log('CREATE', 'study_programs', (string)$newId, null, ['code' => $code, 'name' => $name]);
        $this->redirect('master/study-programs', ['success' => 'Program studi berhasil ditambahkan.']);
    }

    public function updateStudyProgram(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        $code = trim($this->getPost('code', ''));
        $name = trim($this->getPost('name', ''));
        $degree = trim($this->getPost('degree', 'S1'));
        $headName = trim($this->getPost('head_name', ''));
        $accreditationStatus = trim($this->getPost('accreditation_status', 'Baik'));
        $accreditationScore = (int)$this->getPost('accreditation_score', 300);
        $targetRetention = (float)$this->getPost('target_retention', 85.0);
        $studentCount = (int)$this->getPost('student_count', 0);
        $lecturerCount = (int)$this->getPost('lecturer_count', 0);

        if (!$id || empty($code) || empty($name)) {
            $this->redirect('master/study-programs', ['error' => 'Data program studi tidak valid.']);
        }

        $programModel = new StudyProgram();
        $programModel->update($id, [
            'code'                 => $code,
            'name'                 => $name,
            'degree'               => $degree,
            'head_name'            => $headName,
            'accreditation_status' => $accreditationStatus,
            'accreditation_score'  => $accreditationScore,
            'target_retention'     => $targetRetention,
            'student_count'        => $studentCount,
            'lecturer_count'       => $lecturerCount
        ]);

        AuditService::log('UPDATE', 'study_programs', (string)$id, null, ['code' => $code, 'name' => $name]);
        $this->redirect('master/study-programs', ['success' => 'Data program studi berhasil diperbarui.']);
    }

    public function deleteStudyProgram(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $id = (int)$this->getPost('id', 0);
        if ($id) {
            $programModel = new StudyProgram();
            $program = $programModel->find($id);
            $programModel->delete($id);
            AuditService::log('DELETE', 'study_programs', (string)$id, null, ['name' => $program['name'] ?? '']);
            $this->redirect('master/study-programs', ['success' => 'Program studi berhasil dihapus.']);
        }
        $this->redirect('master/study-programs', ['error' => 'Gagal menghapus program studi.']);
    }

    public function academicYears(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $ayModel = new AcademicYear();

        if ($this->isPost()) {
            $this->requireCsrf();
            $setActiveId = (int)$this->getPost('set_active_id', 0);
            if ($setActiveId) {
                // Set all to 0 then selected to 1
                $ayModel->rawQuery("UPDATE academic_years SET is_active = 0");
                $ayModel->update($setActiveId, ['is_active' => 1]);
                AuditService::log('UPDATE_ACTIVE_YEAR', 'academic_years', (string)$setActiveId);
                $this->redirect('master/academic-years', ['success' => 'Tahun akademik aktif berhasil diperbarui.']);
            }
        }

        $academicYears = $ayModel->all('start_date DESC');

        $this->render('master/academic_years', [
            'title'         => 'Data Master Tahun Akademik',
            'academicYears' => $academicYears
        ]);
    }
}
