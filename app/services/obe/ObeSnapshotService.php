<?php
namespace App\Services\Obe;

use App\Core\Database;
use PDO;
use RuntimeException;

final class ObeSnapshotService
{
    private PDO $deis;

    public function __construct(private ?ObeReadConnection $obe = null)
    {
        $this->obe ??= new ObeReadConnection();
        $this->deis = Database::getConnection();
    }

    public function captureAcademicMaster(?int $capturedBy = null): array
    {
        $years = (new AcademicYearRepository($this->obe))->all();
        $activeYear = (new AcademicYearRepository($this->obe))->active();
        $reference = new AcademicReferenceRepository($this->obe);
        $payload = [
            'schema_version' => 1,
            'captured_at' => (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM),
            'active_academic_year' => $activeYear,
            'academic_years' => $years,
            'study_programs' => (new StudyProgramRepository($this->obe))->all(false),
            'lecturers' => (new LecturerRepository($this->obe))->all(),
            'students' => (new StudentRepository($this->obe))->all(),
            'curricula' => (new CurriculumRepository($this->obe))->all(),
            'curriculum_rules' => $reference->curriculumRules(),
            'graduate_profiles' => $reference->graduateProfiles(),
            'learning_outcomes' => $reference->learningOutcomes(),
            'courses' => (new CourseRepository($this->obe))->all(null, null, false),
            'rubrics' => $reference->rubrics(),
            'rps_summary' => $this->obe->fetchAll(
                "SELECT CASE
                    WHEN r.id IS NULL THEN 'Belum Lengkap'
                    WHEN r.sistem_penilaian_desc IS NOT NULL OR r.rencana_evaluasi_json IS NOT NULL THEN 'Lengkap'
                    ELSE 'Revisi' END AS status, COUNT(*) AS total
                 FROM mata_kuliah m LEFT JOIN rps r ON r.mata_kuliah_id=m.id
                 WHERE m.status='ACTIVE' GROUP BY status"
            ),
        ];
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $count = array_sum([
            count($payload['academic_years']), count($payload['study_programs']),
            count($payload['lecturers']), count($payload['students']),
            count($payload['curricula']), count($payload['curriculum_rules']),
            count($payload['graduate_profiles']), count($payload['learning_outcomes']),
            count($payload['courses']), count($payload['rubrics']),
        ]);
        $label = 'Master Akademik OBE - ' . ($activeYear['name'] ?? date('Y-m-d H:i'));
        $checksum = hash('sha256', $json);

        $stmt = $this->deis->prepare(
            'INSERT INTO obe_snapshots
                (snapshot_type, academic_year_obe_id, label, payload_json, record_count, checksum, captured_by)
             VALUES (:type, :year_id, :label, :payload, :record_count, :checksum, :captured_by)'
        );
        $stmt->execute([
            'type' => 'ACADEMIC_MASTER',
            'year_id' => $activeYear['obe_id'] ?? null,
            'label' => $label,
            'payload' => $json,
            'record_count' => $count,
            'checksum' => $checksum,
            'captured_by' => $capturedBy,
        ]);

        return [
            'id' => (int)$this->deis->lastInsertId(), 'label' => $label,
            'record_count' => $count, 'checksum' => $checksum,
        ];
    }

    public function latest(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        return $this->deis->query(
            "SELECT id, snapshot_type, academic_year_obe_id, label, record_count,
                    checksum, captured_by, created_at
             FROM obe_snapshots ORDER BY created_at DESC, id DESC LIMIT {$limit}"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->deis->prepare('SELECT * FROM obe_snapshots WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['payload'] = json_decode($row['payload_json'], true, 512, JSON_THROW_ON_ERROR);
        unset($row['payload_json']);
        return $row;
    }
}
