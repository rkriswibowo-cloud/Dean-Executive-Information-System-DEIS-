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
}
