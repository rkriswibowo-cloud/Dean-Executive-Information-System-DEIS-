<?php
namespace App\Models;

use App\Core\Model;

class Guidance extends Model {
    protected string $table = 'guidances';

    public function allWithDetails(?string $type = null): array {
        $sql = "SELECT g.*, l.name as lecturer_name, s.name as student_name, s.nim, sp.name as program_name
                FROM guidances g
                JOIN lecturers l ON g.lecturer_id = l.id
                JOIN students s ON g.student_id = s.id
                JOIN study_programs sp ON s.study_program_id = sp.id";
        $params = [];
        if ($type) {
            $sql .= " WHERE g.type = :type";
            $params['type'] = $type;
        }
        $sql .= " ORDER BY CASE g.status WHEN 'Terlambat' THEN 1 WHEN 'Bermasalah' THEN 2 ELSE 3 END, g.id DESC";
        return $this->rawFetch($sql, $params);
    }

    public function allObeCases(?string $type = null): array {
        $sql = 'SELECT * FROM guidance_cases';
        $params = [];
        if ($type) {
            $sql .= ' WHERE type = :type';
            $params['type'] = $type;
        }
        $sql .= " ORDER BY CASE status WHEN 'Terlambat' THEN 1 WHEN 'Bermasalah' THEN 2 ELSE 3 END, id DESC";
        $cases = $this->rawFetch($sql, $params);
        foreach ($cases as &$case) {
            $case['student_ids'] = array_map('intval', array_column(
                $this->rawFetch('SELECT obe_student_id FROM guidance_case_students WHERE guidance_case_id=:id ORDER BY obe_student_id', ['id' => $case['id']]),
                'obe_student_id'
            ));
            $case['lecturer_ids'] = array_map('intval', array_column(
                $this->rawFetch('SELECT obe_lecturer_id FROM guidance_case_lecturers WHERE guidance_case_id=:id ORDER BY supervisor_order', ['id' => $case['id']]),
                'obe_lecturer_id'
            ));
        }
        unset($case);
        return $cases;
    }

    public function createObeCase(array $data, array $studentIds, array $lecturerIds): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO guidance_cases (type,title,progress_percentage,status,last_guidance_date,notes) VALUES (:type,:title,:progress,:status,:last_date,:notes)');
            $stmt->execute($data);
            $id = (int)$this->db->lastInsertId();
            $this->replaceParticipants($id, $studentIds, $lecturerIds);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateObeCase(int $id, array $data, array $studentIds, array $lecturerIds): void {
        $this->db->beginTransaction();
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare('UPDATE guidance_cases SET type=:type,title=:title,progress_percentage=:progress,status=:status,last_guidance_date=:last_date,notes=:notes WHERE id=:id');
            $stmt->execute($data);
            $this->replaceParticipants($id, $studentIds, $lecturerIds);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteObeCase(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM guidance_cases WHERE id=:id');
        return $stmt->execute(['id' => $id]);
    }

    public function findObeCase(int $id): ?array {
        foreach ($this->allObeCases() as $case) {
            if ((int)$case['id'] === $id) return $case;
        }
        return null;
    }

    public function sessions(int $caseId): array {
        $sessions = $this->rawFetch('SELECT * FROM guidance_sessions WHERE guidance_case_id=:id ORDER BY guidance_date DESC, id DESC', ['id' => $caseId]);
        foreach ($sessions as &$session) {
            $session['student_ids'] = array_map('intval', array_column(
                $this->rawFetch('SELECT obe_student_id FROM guidance_session_students WHERE guidance_session_id=:id ORDER BY obe_student_id', ['id' => $session['id']]),
                'obe_student_id'
            ));
        }
        unset($session);
        return $sessions;
    }

    public function createSession(int $caseId, array $data, array $studentIds): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO guidance_sessions (guidance_case_id,guidance_date,topic,notes,progress_after) VALUES (:case_id,:guidance_date,:topic,:notes,:progress_after)');
            $stmt->execute(['case_id'=>$caseId] + $data);
            $id = (int)$this->db->lastInsertId();
            $participant = $this->db->prepare('INSERT INTO guidance_session_students (guidance_session_id,obe_student_id) VALUES (:session_id,:student_id)');
            foreach ($studentIds as $studentId) $participant->execute(['session_id'=>$id, 'student_id'=>$studentId]);
            if ($data['progress_after'] !== null) {
                $this->rawQuery('UPDATE guidance_cases SET progress_percentage=:progress,last_guidance_date=:date WHERE id=:id', ['progress'=>$data['progress_after'], 'date'=>$data['guidance_date'], 'id'=>$caseId]);
            } else {
                $this->rawQuery('UPDATE guidance_cases SET last_guidance_date=:date WHERE id=:id', ['date'=>$data['guidance_date'], 'id'=>$caseId]);
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteSession(int $sessionId, int $caseId): bool {
        $stmt = $this->db->prepare('DELETE FROM guidance_sessions WHERE id=:id AND guidance_case_id=:case_id');
        return $stmt->execute(['id'=>$sessionId, 'case_id'=>$caseId]);
    }

    private function replaceParticipants(int $id, array $studentIds, array $lecturerIds): void {
        $this->rawQuery('DELETE FROM guidance_case_students WHERE guidance_case_id=:id', ['id' => $id]);
        $this->rawQuery('DELETE FROM guidance_case_lecturers WHERE guidance_case_id=:id', ['id' => $id]);
        $studentStmt = $this->db->prepare('INSERT INTO guidance_case_students (guidance_case_id,obe_student_id) VALUES (:case_id,:obe_id)');
        foreach ($studentIds as $obeId) $studentStmt->execute(['case_id' => $id, 'obe_id' => $obeId]);
        $lecturerStmt = $this->db->prepare('INSERT INTO guidance_case_lecturers (guidance_case_id,obe_lecturer_id,supervisor_order) VALUES (:case_id,:obe_id,:position)');
        foreach (array_values($lecturerIds) as $position => $obeId) {
            $lecturerStmt->execute(['case_id' => $id, 'obe_id' => $obeId, 'position' => $position + 1]);
        }
    }
}
