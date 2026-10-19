<?php
namespace App\Models;

use App\Core\Model;

class Course extends Model {
    protected string $table = 'courses';

    public function allWithLecturerAndProgram(?int $programId = null): array {
        $sql = "SELECT c.*, sp.name as program_name, l.name as lecturer_name 
                FROM courses c 
                JOIN study_programs sp ON c.study_program_id = sp.id 
                LEFT JOIN lecturers l ON c.lecturer_id = l.id";
        $params = [];
        if ($programId) {
            $sql .= " WHERE c.study_program_id = :pid";
            $params['pid'] = $programId;
        }
        $sql .= " ORDER BY c.semester ASC, c.code ASC";
        return $this->rawFetch($sql, $params);
    }

    public function getRpsSummary(): array {
        $sql = "SELECT 
                    COUNT(*) as total_courses,
                    SUM(CASE WHEN rps_status = 'Lengkap' THEN 1 ELSE 0 END) as complete_count,
                    SUM(CASE WHEN rps_status = 'Revisi' THEN 1 ELSE 0 END) as revision_count,
                    SUM(CASE WHEN rps_status = 'Belum Lengkap' THEN 1 ELSE 0 END) as incomplete_count,
                    SUM(CASE WHEN case_method_flag = 1 THEN 1 ELSE 0 END) as case_method_count
                FROM courses";
        $res = $this->rawFetch($sql);
        return $res[0] ?? [
            'total_courses' => 0,
            'complete_count' => 0,
            'revision_count' => 0,
            'incomplete_count' => 0,
            'case_method_count' => 0
        ];
    }

    public function getRpsByProdi(): array {
        $sql = "SELECT sp.id as program_id, sp.name as program_name, sp.degree,
                       COUNT(c.id) as total_courses,
                       SUM(CASE WHEN c.rps_status = 'Lengkap' THEN 1 ELSE 0 END) as complete_count,
                       SUM(CASE WHEN c.rps_status = 'Revisi' THEN 1 ELSE 0 END) as revision_count,
                       SUM(CASE WHEN c.rps_status = 'Belum Lengkap' THEN 1 ELSE 0 END) as incomplete_count,
                       SUM(CASE WHEN c.case_method_flag = 1 THEN 1 ELSE 0 END) as case_method_count
                FROM study_programs sp
                LEFT JOIN courses c ON sp.id = c.study_program_id
                GROUP BY sp.id, sp.name, sp.degree
                ORDER BY sp.id ASC";
        return $this->rawFetch($sql);
    }
}
