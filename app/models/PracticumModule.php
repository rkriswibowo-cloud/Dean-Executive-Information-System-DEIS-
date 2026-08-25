<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class PracticumModule extends Model {
    protected string $table = 'practicum_modules';

    /**
     * Get all practicum modules with joined study program and academic year
     */
    public function allWithDetails(?int $studyProgramId = null, ?int $semester = null, ?string $status = null): array {
        $sql = "
            SELECT 
                pm.*,
                sp.name AS program_name,
                sp.code AS program_code,
                sp.head_name AS kaprodi_name,
                ay.name AS academic_year_name,
                ay.semester AS academic_semester,
                ROUND((pm.completed_modules / pm.target_modules) * 100, 1) AS completion_percentage
            FROM {$this->table} pm
            JOIN study_programs sp ON pm.study_program_id = sp.id
            LEFT JOIN academic_years ay ON pm.academic_year_id = ay.id
            WHERE 1=1
        ";
        $params = [];

        if ($studyProgramId !== null && $studyProgramId > 0) {
            $sql .= " AND pm.study_program_id = :sp_id";
            $params['sp_id'] = $studyProgramId;
        }

        if ($semester !== null && $semester > 0) {
            $sql .= " AND pm.semester = :semester";
            $params['semester'] = $semester;
        }

        if (!empty($status)) {
            $sql .= " AND pm.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY sp.id ASC, pm.semester ASC, pm.course_code ASC";

        return $this->rawFetch($sql, $params);
    }

    /**
     * Find single practicum module with details
     */
    public function findWithDetails(int $id): ?array {
        $sql = "
            SELECT 
                pm.*,
                sp.name AS program_name,
                sp.code AS program_code,
                sp.head_name AS kaprodi_name,
                ay.name AS academic_year_name,
                ROUND((pm.completed_modules / pm.target_modules) * 100, 1) AS completion_percentage
            FROM {$this->table} pm
            JOIN study_programs sp ON pm.study_program_id = sp.id
            LEFT JOIN academic_years ay ON pm.academic_year_id = ay.id
            WHERE pm.id = :id
            LIMIT 1
        ";
        return $this->rawFetchOne($sql, ['id' => $id]);
    }

    /**
     * Get aggregate statistics per study program and semester matrix
     */
    public function getSummaryByProdi(): array {
        $sql = "
            SELECT 
                sp.id AS program_id,
                sp.name AS program_name,
                sp.code AS program_code,
                sp.head_name AS kaprodi_name,
                pm.semester,
                COUNT(pm.id) AS total_courses,
                SUM(pm.target_modules) AS total_target_modules,
                SUM(pm.completed_modules) AS total_completed_modules,
                ROUND((SUM(pm.completed_modules) / SUM(pm.target_modules)) * 100, 1) AS achievement_rate,
                SUM(CASE WHEN pm.completed_modules >= pm.target_modules THEN 1 ELSE 0 END) AS fully_completed_courses,
                SUM(CASE WHEN pm.status = 'Dikonfirmasi ke Kaprodi' THEN 1 ELSE 0 END) AS confirmed_to_kaprodi_count,
                SUM(CASE WHEN pm.completed_modules < pm.target_modules THEN 1 ELSE 0 END) AS gap_courses_count
            FROM study_programs sp
            JOIN {$this->table} pm ON sp.id = pm.study_program_id
            GROUP BY sp.id, sp.name, sp.code, sp.head_name, pm.semester
            ORDER BY sp.id ASC, pm.semester ASC
        ";

        return $this->rawFetch($sql);
    }

    /**
     * Get overall high-level statistics
     */
    public function getOverallStats(): array {
        $sql = "
            SELECT 
                COUNT(id) AS total_labs,
                SUM(target_modules) AS sum_target_modules,
                SUM(completed_modules) AS sum_completed_modules,
                ROUND((SUM(completed_modules) / NULLIF(SUM(target_modules), 0)) * 100, 1) AS overall_completion_rate,
                SUM(CASE WHEN completed_modules >= target_modules THEN 1 ELSE 0 END) AS count_100_percent,
                SUM(CASE WHEN status = 'Dikonfirmasi ke Kaprodi' THEN 1 ELSE 0 END) AS count_confirmed_kaprodi,
                SUM(CASE WHEN completed_modules < target_modules THEN 1 ELSE 0 END) AS count_attention_needed
            FROM {$this->table}
        ";

        $res = $this->rawFetchOne($sql);
        return $res ?: [
            'total_labs' => 0,
            'sum_target_modules' => 0,
            'sum_completed_modules' => 0,
            'overall_completion_rate' => 0,
            'count_100_percent' => 0,
            'count_confirmed_kaprodi' => 0,
            'count_attention_needed' => 0,
        ];
    }

    /**
     * Dekan confirms / pushes note to Kaprodi
     */
    public function confirmToKaprodi(int $id, string $notes): bool {
        $sql = "
            UPDATE {$this->table}
            SET 
                status = 'Dikonfirmasi ke Kaprodi',
                dekan_notes = :notes,
                last_confirmed_at = NOW()
            WHERE id = :id
        ";
        return $this->rawQuery($sql, [
            'id'    => $id,
            'notes' => $notes
        ])->rowCount() > 0;
    }

    /**
     * Update progress and module fulfillment
     */
    public function updateProgress(int $id, array $data): bool {
        $target = (int)($data['target_modules'] ?? 12);
        $completed = (int)($data['completed_modules'] ?? 0);
        
        $status = $data['status'] ?? null;
        if (!$status) {
            if ($completed >= $target) {
                $status = 'Terpenuhi 100%';
            } elseif ($completed > 0) {
                $status = 'Progres Berjalan';
            } else {
                $status = 'Perlu Perhatian';
            }
        }

        $sql = "
            UPDATE {$this->table}
            SET 
                target_modules = :target,
                completed_modules = :completed,
                lab_name = :lab_name,
                lecturer_name = :lecturer_name,
                assistant_name = :assistant_name,
                logbook_status = :logbook_status,
                status = :status,
                dekan_notes = :dekan_notes,
                kaprodi_feedback = :kaprodi_feedback
            WHERE id = :id
        ";

        return $this->rawQuery($sql, [
            'id'               => $id,
            'target'           => $target,
            'completed'        => $completed,
            'lab_name'         => $data['lab_name'] ?? '',
            'lecturer_name'    => $data['lecturer_name'] ?? '',
            'assistant_name'   => $data['assistant_name'] ?? '',
            'logbook_status'   => $data['logbook_status'] ?? 'Lengkap',
            'status'           => $status,
            'dekan_notes'      => $data['dekan_notes'] ?? null,
            'kaprodi_feedback' => $data['kaprodi_feedback'] ?? null
        ])->rowCount() > 0;
    }
}
