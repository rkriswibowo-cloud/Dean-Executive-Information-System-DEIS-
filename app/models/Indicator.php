<?php
namespace App\Models;

use App\Core\Model;

class Indicator extends Model {
    protected string $table = 'indicators';

    public function allWithTargetAndRealization(int $year = 2026, bool $onlyActive = true): array {
        $whereClause = $onlyActive ? "WHERE i.is_active = 1" : "";
        $sql = "SELECT i.*, 
                       it.id as target_id, it.target_value, it.period,
                       ir.id as realization_id, ir.realization_value, 
                       LEAST(100.0, IFNULL(ir.achievement_percentage, 0.0)) as achievement_percentage,
                       ir.achievement_percentage as raw_achievement_percentage,
                       ir.status as realization_status, ir.notes as realization_notes, ir.verified_by
                FROM indicators i
                LEFT JOIN indicator_targets it ON i.id = it.indicator_id AND it.year = :year
                LEFT JOIN indicator_realizations ir ON it.id = ir.indicator_target_id
                {$whereClause}
                ORDER BY i.id ASC";
        return $this->rawFetch($sql, ['year' => $year]);
    }

    public function findByCode(string $code, ?int $excludeId = null): ?array {
        $sql = "SELECT * FROM indicators WHERE code = :code";
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->rawFetch($sql, $params);
        return $res[0] ?? null;
    }
}
