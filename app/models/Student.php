<?php
namespace App\Models;

use App\Core\Model;

class Student extends Model {
    protected string $table = 'students';

    public function allWithProgram(?int $programId = null, ?string $riskStatus = null): array {
        $sql = "SELECT s.*, sp.name as program_name, sp.code as program_code 
                FROM students s 
                JOIN study_programs sp ON s.study_program_id = sp.id
                WHERE 1=1";
        $params = [];
        if ($programId) {
            $sql .= " AND s.study_program_id = :pid";
            $params['pid'] = $programId;
        }
        if ($riskStatus) {
            $sql .= " AND s.risk_status = :rstatus";
            $params['rstatus'] = $riskStatus;
        }
        $sql .= " ORDER BY s.id DESC";
        return $this->rawFetch($sql, $params);
    }

    public function getEarlyWarningList(): array {
        $sql = "SELECT s.*, sp.name as program_name, sp.code as program_code 
                FROM students s 
                JOIN study_programs sp ON s.study_program_id = sp.id
                WHERE s.risk_status IN ('Critical', 'Warning') AND s.status = 'Aktif'
                ORDER BY CASE s.risk_status WHEN 'Critical' THEN 1 ELSE 2 END, s.current_gpa ASC";
        return $this->rawFetch($sql);
    }
}
