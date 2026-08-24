<?php
namespace App\Models;

use App\Core\Model;

class Finance extends Model {
    protected string $table = 'finances';

    public function allWithProgram(?int $year = 2026): array {
        $sql = "SELECT f.*, sp.name as program_name 
                FROM finances f 
                LEFT JOIN study_programs sp ON f.study_program_id = sp.id 
                WHERE f.fiscal_year = :year 
                ORDER BY f.id ASC";
        return $this->rawFetch($sql, ['year' => $year]);
    }
}
