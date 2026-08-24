<?php
namespace App\Models;

use App\Core\Model;

class Lecturer extends Model {
    protected string $table = 'lecturers';

    public function allWithProgram(?int $programId = null): array {
        $sql = "SELECT l.*, sp.name as program_name, sp.code as program_code 
                FROM lecturers l 
                JOIN study_programs sp ON l.study_program_id = sp.id";
        $params = [];
        if ($programId) {
            $sql .= " WHERE l.study_program_id = :pid";
            $params['pid'] = $programId;
        }
        $sql .= " ORDER BY l.name ASC";
        return $this->rawFetch($sql, $params);
    }

    public function getKpiRanking(): array {
        $sql = "SELECT l.*, sp.name as program_name,
                (l.sinta_score * 0.4 + l.publication_count * 15 + l.pkm_count * 10 + l.hki_count * 20 + l.attendance_percentage * 0.5) as kpi_total_score
                FROM lecturers l
                JOIN study_programs sp ON l.study_program_id = sp.id
                WHERE l.status = 'Aktif'
                ORDER BY kpi_total_score DESC";
        return $this->rawFetch($sql);
    }

    public function findByNidn(string $nidn): ?array {
        return $this->whereOne('nidn', $nidn);
    }

    public function findByUserId(int $userId): ?array {
        return $this->whereOne('user_id', $userId);
    }
}
