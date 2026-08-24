<?php
namespace App\Models;

use App\Core\Model;

class Accreditation extends Model {
    protected string $table = 'accreditations';

    public function allWithProgram(): array {
        $sql = "SELECT a.*, sp.name as program_name, sp.degree,
                       DATEDIFF(a.valid_until, CURDATE()) as calculated_days_remaining
                FROM accreditations a
                JOIN study_programs sp ON a.study_program_id = sp.id
                ORDER BY a.valid_until ASC";
        return $this->rawFetch($sql);
    }
}
