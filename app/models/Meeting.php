<?php
namespace App\Models;

use App\Core\Model;

class Meeting extends Model {
    protected string $table = 'meetings';

    public function allWithDetails(): array {
        $sql = "SELECT m.*, 
                       u1.name as chairperson_name, 
                       u2.name as secretary_name,
                       (SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = m.id) as participant_count,
                       (SELECT COUNT(*) FROM meeting_documents WHERE meeting_id = m.id) as document_count,
                       (SELECT COUNT(*) FROM action_items WHERE meeting_id = m.id) as rtl_count
                FROM meetings m
                LEFT JOIN users u1 ON m.chairperson_id = u1.id
                LEFT JOIN users u2 ON m.secretary_id = u2.id
                ORDER BY m.meeting_date DESC, m.start_time DESC";
        return $this->rawFetch($sql);
    }

    public function getDigitalPacket(int $meetingId): array {
        $meeting = $this->find($meetingId);
        if (!$meeting) return [];

        $db = $this->getDb();

        // Participants
        $stmt = $db->prepare("SELECT * FROM meeting_participants WHERE meeting_id = :id ORDER BY id ASC");
        $stmt->execute(['id' => $meetingId]);
        $meeting['participants'] = $stmt->fetchAll();

        // Documents
        $stmt = $db->prepare("SELECT * FROM meeting_documents WHERE meeting_id = :id ORDER BY id ASC");
        $stmt->execute(['id' => $meetingId]);
        $meeting['documents'] = $stmt->fetchAll();

        // Action items (RTL)
        $stmt = $db->prepare("SELECT * FROM action_items WHERE meeting_id = :id ORDER BY id ASC");
        $stmt->execute(['id' => $meetingId]);
        $meeting['action_items'] = $stmt->fetchAll();

        return $meeting;
    }
}
