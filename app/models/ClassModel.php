<?php
namespace App\Models;

use App\Core\Model;

class ClassModel extends Model {
    protected string $table = 'classes';

    public function allWithDetails(?int $academicYearId = null): array {
        $sql = "SELECT cl.*, c.name as course_name, c.code as course_code, c.sks,
                       l.name as lecturer_name, sp.name as program_name, sp.id as program_id,
                       ay.name as academic_year_name, ay.semester as academic_semester
                FROM classes cl
                JOIN courses c ON cl.course_id = c.id
                JOIN study_programs sp ON c.study_program_id = sp.id
                JOIN lecturers l ON cl.lecturer_id = l.id
                JOIN academic_years ay ON cl.academic_year_id = ay.id";
        $params = [];
        if ($academicYearId) {
            $sql .= " WHERE cl.academic_year_id = :ayid";
            $params['ayid'] = $academicYearId;
        }
        $sql .= " ORDER BY cl.problem_flag DESC, cl.id ASC";
        return $this->rawFetch($sql, $params);
    }

    public function findWithDetails(int $id): ?array {
        $sql = "SELECT cl.*, c.name as course_name, c.code as course_code, c.sks, c.semester as course_semester,
                       c.rps_status, c.case_method_flag,
                       l.name as lecturer_name, l.nidn as lecturer_nidn,
                       sp.name as program_name, sp.id as program_id,
                       ay.name as academic_year_name, ay.semester as academic_semester
                FROM classes cl
                JOIN courses c ON cl.course_id = c.id
                JOIN study_programs sp ON c.study_program_id = sp.id
                JOIN lecturers l ON cl.lecturer_id = l.id
                JOIN academic_years ay ON cl.academic_year_id = ay.id
                WHERE cl.id = :id LIMIT 1";
        return $this->rawFetchOne($sql, ['id' => $id]);
    }
}
