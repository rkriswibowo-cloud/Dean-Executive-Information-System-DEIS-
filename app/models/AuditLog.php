<?php
namespace App\Models;

use App\Core\Model;

class AuditLog extends Model {
    protected string $table = 'audit_logs';

    public function allWithFilter(?string $module = null, ?string $action = null, int $limit = 100): array {
        $sql = "SELECT * FROM `audit_logs` WHERE 1=1";
        $params = [];
        if ($module) {
            $sql .= " AND module = :module";
            $params['module'] = $module;
        }
        if ($action) {
            $sql .= " AND action = :action";
            $params['action'] = $action;
        }
        $sql .= " ORDER BY id DESC LIMIT {$limit}";
        return $this->rawFetch($sql, $params);
    }
}
