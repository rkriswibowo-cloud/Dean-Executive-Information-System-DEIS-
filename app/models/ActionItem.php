<?php
namespace App\Models;

use App\Core\Model;

class ActionItem extends Model {
    protected string $table = 'action_items';

    public function allWithMeetingAndProgram(): array {
        $sql = "SELECT ai.*, m.title as meeting_title, m.meeting_number, sp.name as program_name,
                       DATEDIFF(ai.deadline, CURDATE()) as days_left
                FROM action_items ai
                JOIN meetings m ON ai.meeting_id = m.id
                LEFT JOIN study_programs sp ON ai.study_program_id = sp.id
                ORDER BY CASE ai.status WHEN 'Terlambat' THEN 1 WHEN 'Proses' THEN 2 WHEN 'Diserahkan' THEN 3 ELSE 4 END, ai.deadline ASC";
        return $this->rawFetch($sql);
    }
}
