<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\AcademicYear;
use App\Services\AuditService;
use App\Services\Obe\AcademicReferenceRepository;
use App\Services\Obe\AcademicYearRepository as ObeAcademicYearRepository;
use App\Services\Obe\CourseRepository as ObeCourseRepository;
use App\Services\Obe\CurriculumRepository as ObeCurriculumRepository;
use App\Services\Obe\LecturerRepository as ObeLecturerRepository;
use App\Services\Obe\ObeHealthService;
use App\Services\Obe\ObeMappingService;
use App\Services\Obe\ObeReadinessService;
use App\Services\Obe\StudentRepository as ObeStudentRepository;
use App\Services\Obe\StudyProgramRepository as ObeStudyProgramRepository;

class MasterController extends Controller {
    public function obeStatus(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'dekan', 'developer']);

        $this->render('master/obe_status', [
            'title' => 'Status Kesiapan Integrasi OBE',
            'report' => (new ObeReadinessService())->report(),
        ]);
    }

    public function obeMaster(): void {
        $this->requireAuth();

        $allowedSections = [
            'academic-years', 'lecturers', 'students', 'study-programs', 'curricula',
            'curriculum-rules', 'graduate-profiles', 'learning-outcomes', 'courses',
            'rubrics', 'imports'
        ];
        $section = (string)$this->getQuery('section', 'study-programs');
        if (!in_array($section, $allowedSections, true)) {
            $section = 'study-programs';
        }

        $health = (new ObeHealthService())->check();
        $records = [];
        $counts = array_fill_keys($allowedSections, null);
        $studyPrograms = [];
        $curricula = [];

        $filterProdiId = $this->getQuery('prodi_id');
        $filterProdiId = ($filterProdiId !== null && $filterProdiId !== '') ? (int)$filterProdiId : null;

        $filterCurriculumId = $this->getQuery('kurikulum_id');
        $filterCurriculumId = ($filterCurriculumId !== null && $filterCurriculumId !== '') ? (int)$filterCurriculumId : null;

        $searchQuery = trim((string)$this->getQuery('q', ''));

        if ($health['available']) {
            $reference = new AcademicReferenceRepository();
            $studyPrograms = (new ObeStudyProgramRepository())->all(false);
            $curricula = (new ObeCurriculumRepository())->all(null, false);

            $records = match ($section) {
                'academic-years' => (new ObeAcademicYearRepository())->all(),
                'lecturers' => (new ObeLecturerRepository())->all(),
                'students' => (new ObeStudentRepository())->all(),
                'study-programs' => $studyPrograms,
                'curricula' => (new ObeCurriculumRepository())->all($filterProdiId, false),
                'curriculum-rules' => $reference->curriculumRules(),
                'graduate-profiles' => $reference->graduateProfiles(),
                'learning-outcomes' => $reference->learningOutcomes(),
                'courses' => (new ObeCourseRepository())->all($filterProdiId, $filterCurriculumId, false),
                'rubrics' => $reference->rubrics(),
                default => [],
            };
            $counts = $reference->counts();

            // Client-side/memory filtering for common fields (prodi, curriculum, search query)
            if ($filterProdiId !== null && !in_array($section, ['curricula', 'courses'], true)) {
                $records = array_values(array_filter($records, static function (array $row) use ($filterProdiId): bool {
                    if (isset($row['study_program_id'])) {
                        return (int)$row['study_program_id'] === $filterProdiId;
                    }
                    if (isset($row['prodi_id'])) {
                        return (int)$row['prodi_id'] === $filterProdiId;
                    }
                    if (isset($row['obe_id']) && isset($row['code']) && isset($row['degree'])) {
                        // Untuk section study-programs jika difilter
                        return (int)$row['obe_id'] === $filterProdiId;
                    }
                    return true;
                }));
            }

            if ($filterCurriculumId !== null && $section !== 'courses') {
                $records = array_values(array_filter($records, static function (array $row) use ($filterCurriculumId): bool {
                    if (isset($row['curriculum_id'])) {
                        return (int)$row['curriculum_id'] === $filterCurriculumId;
                    }
                    if (isset($row['kurikulum_id'])) {
                        return (int)$row['kurikulum_id'] === $filterCurriculumId;
                    }
                    if (isset($row['obe_id']) && isset($row['start_year'])) {
                        // Untuk section curricula
                        return (int)$row['obe_id'] === $filterCurriculumId;
                    }
                    return true;
                }));
            }

            if ($searchQuery !== '') {
                $qLower = mb_strtolower($searchQuery, 'UTF-8');
                $records = array_values(array_filter($records, static function (array $row) use ($qLower): bool {
                    foreach ($row as $val) {
                        if (is_scalar($val) && str_contains(mb_strtolower((string)$val, 'UTF-8'), $qLower)) {
                            return true;
                        }
                    }
                    return false;
                }));
            }
        }

        try {
            $mappingSummary = (new ObeMappingService())->summary();
        } catch (\Throwable $e) {
            $mappingSummary = [];
        }

        $this->render('master/obe', [
            'title' => 'Master Data Akademik OBE',
            'section' => $section,
            'sections' => $allowedSections,
            'records' => $records,
            'counts' => $counts,
            'health' => $health,
            'mappingSummary' => $mappingSummary,
            'studyPrograms' => $studyPrograms,
            'curricula' => $curricula,
            'filterProdiId' => $filterProdiId,
            'filterCurriculumId' => $filterCurriculumId,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function reconcileObeMappings(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $this->requireCsrf();
        try {
            $result = (new ObeMappingService())->reconcile();
            AuditService::log('RECONCILE_OBE_MAPPING', 'obe_entity_mappings', null, null, $result);
            $this->redirect('master/obe', ['success' => 'Rekonsiliasi ID OBE–DEIS berhasil diperbarui.']);
        } catch (\Throwable $e) {
            $this->redirect('master/obe', ['error' => 'Rekonsiliasi gagal: ' . $e->getMessage()]);
        }
    }

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
        $this->redirect('master/obe?section=study-programs');
    }

    public function createStudyProgram(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $this->redirect('master/obe?section=study-programs', ['error' => 'Master Program Studi bersifat read-only di DEIS. Kelola data melalui OBE.']);

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
        $this->redirect('master/obe?section=study-programs', ['error' => 'Master Program Studi bersifat read-only di DEIS. Kelola data melalui OBE.']);

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
        $this->redirect('master/obe?section=study-programs', ['error' => 'Master Program Studi bersifat read-only di DEIS. Kelola data melalui OBE.']);

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
        if ($this->isPost()) {
            $this->requireCsrf();
            $this->redirect('master/obe?section=academic-years', ['error' => 'Tahun Akademik bersifat read-only di DEIS. Kelola periode aktif melalui OBE.']);
        }
        $this->redirect('master/obe?section=academic-years');
    }
}
