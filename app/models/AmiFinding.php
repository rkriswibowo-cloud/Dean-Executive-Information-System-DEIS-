<?php
namespace App\Models;

use App\Core\Model;

class AmiFinding extends Model {
    protected string $table = 'ami_findings';

    public function allWithAuditAndStandard(): array {
        $sql = "SELECT f.*, a.audit_date, sp.name as program_name, std.code as standard_code, std.name as standard_name
                FROM ami_findings f
                JOIN ami_audits a ON f.ami_audit_id = a.id
                JOIN study_programs sp ON a.study_program_id = sp.id
                LEFT JOIN spmi_standards std ON f.standard_id = std.id
                ORDER BY CASE f.status WHEN 'Open' THEN 1 WHEN 'In Progress' THEN 2 ELSE 3 END, f.deadline ASC";
        return $this->rawFetch($sql);
    }
}
