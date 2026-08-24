<?php
namespace App\Models;

use App\Core\Model;

class Indicator extends Model {
    protected string $table = 'indicators';

    public function allWithTargetAndRealization(int $year = 2026): array {
        $sql = "SELECT i.*, 
                       it.id as target_id, it.target_value, it.period,
                       ir.id as realization_id, ir.realization_value, ir.achievement_percentage, 
                       ir.status as realization_status, ir.notes as realization_notes, ir.verified_by
                FROM indicators i
                LEFT JOIN indicator_targets it ON i.id = it.indicator_id AND it.year = :year
                LEFT JOIN indicator_realizations ir ON it.id = ir.indicator_target_id
                WHERE i.is_active = 1
                ORDER BY i.id ASC";
        return $this->rawFetch($sql, ['year' => $year]);
    }
}
