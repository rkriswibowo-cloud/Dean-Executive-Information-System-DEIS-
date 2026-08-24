<?php
namespace App\Models;

use App\Core\Model;

class Guidance extends Model {
    protected string $table = 'guidances';

    public function allWithDetails(?string $type = null): array {
        $sql = "SELECT g.*, l.name as lecturer_name, s.name as student_name, s.nim, sp.name as program_name
                FROM guidances g
                JOIN lecturers l ON g.lecturer_id = l.id
                JOIN students s ON g.student_id = s.id
                JOIN study_programs sp ON s.study_program_id = sp.id";
        $params = [];
        if ($type) {
            $sql .= " WHERE g.type = :type";
            $params['type'] = $type;
        }
        $sql .= " ORDER BY CASE g.status WHEN 'Terlambat' THEN 1 WHEN 'Bermasalah' THEN 2 ELSE 3 END, g.id DESC";
        return $this->rawFetch($sql, $params);
    }
}
