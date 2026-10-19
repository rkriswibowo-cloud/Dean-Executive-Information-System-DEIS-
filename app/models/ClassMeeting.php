<?php
namespace App\Models;

use App\Core\Model;

class ClassMeeting extends Model {
    protected string $table = 'class_meetings';

    public function byClass(int $classId): array {
        $sql = "SELECT cm.*, cl.class_name, cl.room as default_room,
                       c.name as course_name, c.code as course_code, c.sks,
                       l.name as lecturer_name, l.nidn as lecturer_nidn
                FROM class_meetings cm
                JOIN classes cl ON cm.class_id = cl.id
                JOIN courses c ON cl.course_id = c.id
                JOIN lecturers l ON cl.lecturer_id = l.id
                WHERE cm.class_id = :cid
                ORDER BY cm.meeting_no ASC";
        return $this->rawFetch($sql, ['cid' => $classId]);
    }

    public function getSummaryByClass(int $classId): array {
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = 'Terlaksana' THEN 1 ELSE 0 END) as held_count,
                    SUM(CASE WHEN status = 'Batal' THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(CASE WHEN status = 'Direncanakan' THEN 1 ELSE 0 END) as planned_count,
                    AVG(CASE WHEN status = 'Terlaksana' THEN attendance_percentage ELSE NULL END) as avg_attendance
                FROM class_meetings
                WHERE class_id = :cid";
        $res = $this->rawFetch($sql, ['cid' => $classId]);
        return $res[0] ?? [
            'total_records' => 0,
            'held_count' => 0,
            'cancelled_count' => 0,
            'planned_count' => 0,
            'avg_attendance' => 0
        ];
    }
}
