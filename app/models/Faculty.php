<?php
namespace App\Models;

use App\Core\Model;

class Faculty extends Model {
    protected string $table = 'faculties';

    public function allWithStats(): array {
        $sql = "SELECT f.*, 
                COUNT(DISTINCT sp.id) as study_programs_count,
                COALESCE(SUM(sp.student_count), 0) as total_students,
                COALESCE(SUM(sp.lecturer_count), 0) as total_lecturers
                FROM faculties f
                LEFT JOIN study_programs sp ON f.id = sp.faculty_id
                GROUP BY f.id
                ORDER BY f.id ASC";
        return $this->rawFetch($sql);
    }
}
