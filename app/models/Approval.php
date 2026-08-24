<?php
namespace App\Models;

use App\Core\Model;

class Approval extends Model {
    protected string $table = 'approvals';

    public function allWithDetails(?int $userId = null, ?int $programId = null, bool $isAdminOrDean = false): array {
        $sql = "SELECT a.*, sp.name as program_name, u.name as requester_full_name
                FROM approvals a
                LEFT JOIN study_programs sp ON a.study_program_id = sp.id
                LEFT JOIN users u ON a.requester_id = u.id";
        
        $params = [];
        if (!$isAdminOrDean) {
            if ($programId) {
                $sql .= " WHERE (a.study_program_id = :pid OR a.requester_id = :uid)";
                $params['pid'] = $programId;
                $params['uid'] = $userId;
            } elseif ($userId) {
                $sql .= " WHERE a.requester_id = :uid";
                $params['uid'] = $userId;
            }
        }

        $sql .= " ORDER BY CASE a.status WHEN 'Pending' THEN 1 ELSE 2 END, a.submission_date DESC";
        return $this->rawFetch($sql, $params);
    }
}
