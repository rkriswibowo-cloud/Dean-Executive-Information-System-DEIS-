<?php
namespace App\Models;

use App\Core\Model;

class AmiAudit extends Model {
    protected string $table = 'ami_audits';

    public function allWithProgram(): array {
        $sql = "SELECT a.*, sp.name as program_name 
                FROM ami_audits a 
                JOIN study_programs sp ON a.study_program_id = sp.id 
                ORDER BY a.period_year DESC, a.audit_date DESC";
        return $this->rawFetch($sql);
    }
}
