<?php
namespace App\Services\Obe;

use App\Core\Database;
use PDO;

/** Menyimpan relasi ID di database DEIS; tidak pernah menulis ke OBE. */
final class ObeMappingService
{
    private PDO $deis;

    public function __construct(private ?ObeReadConnection $obe = null)
    {
        $this->obe ??= new ObeReadConnection();
        $this->deis = Database::getConnection();
    }

    public function reconcile(): array
    {
        $definitions = [
            'academic_year' => [
                (new AcademicYearRepository($this->obe))->all(),
                $this->deis->query("SELECT id, CONCAT(name, ' ', semester) AS natural_key FROM academic_years")->fetchAll(),
                static fn(array $row): string => self::key($row['name']),
            ],
            'study_program' => [
                (new StudyProgramRepository($this->obe))->all(false),
                $this->deis->query('SELECT id, code AS natural_key FROM study_programs')->fetchAll(),
                static fn(array $row): string => self::key($row['code']),
            ],
            'lecturer' => [
                (new LecturerRepository($this->obe))->all(),
                $this->deis->query('SELECT id, nidn AS natural_key FROM lecturers')->fetchAll(),
                static fn(array $row): string => self::key($row['nidn'] ?? ''),
            ],
            'student' => [
                (new StudentRepository($this->obe))->all(),
                $this->deis->query('SELECT id, nim AS natural_key FROM students')->fetchAll(),
                static fn(array $row): string => self::key($row['username']),
            ],
            'course' => [
                (new CourseRepository($this->obe))->all(null, null, false),
                $this->deis->query('SELECT id, code AS natural_key FROM courses')->fetchAll(),
                static fn(array $row): string => self::key($row['code']),
            ],
        ];

        $result = [];
        $upsert = $this->deis->prepare(
            "INSERT INTO obe_entity_mappings
                (entity_type, obe_id, deis_id, natural_key, match_status, matched_by, last_checked_at)
             VALUES (:entity_type, :obe_id, :deis_id, :natural_key, :match_status, :matched_by, NOW())
             ON DUPLICATE KEY UPDATE deis_id=VALUES(deis_id), natural_key=VALUES(natural_key),
                match_status=VALUES(match_status), matched_by=VALUES(matched_by), last_checked_at=NOW()"
        );

        foreach ($definitions as $entityType => [$obeRows, $localRows, $obeKey]) {
            $localIndex = [];
            foreach ($localRows as $localRow) {
                $key = self::key($localRow['natural_key'] ?? '');
                if ($key !== '') {
                    $localIndex[$key][] = (int)$localRow['id'];
                }
            }

            $obeKeyFrequency = [];
            foreach ($obeRows as $obeRow) {
                $key = $obeKey($obeRow);
                if ($key !== '') {
                    $obeKeyFrequency[$key] = ($obeKeyFrequency[$key] ?? 0) + 1;
                }
            }

            $stats = ['total' => 0, 'mapped' => 0, 'unmapped' => 0, 'conflict' => 0];
            foreach ($obeRows as $obeRow) {
                $key = $obeKey($obeRow);
                $matches = $key !== '' ? ($localIndex[$key] ?? []) : [];
                $ambiguousObeKey = $key !== '' && ($obeKeyFrequency[$key] ?? 0) > 1;
                $status = $ambiguousObeKey || count($matches) > 1
                    ? 'CONFLICT'
                    : (count($matches) === 1 ? 'MAPPED' : 'UNMAPPED');
                $deisId = $status === 'MAPPED' ? $matches[0] : null;
                $upsert->execute([
                    'entity_type' => $entityType,
                    'obe_id' => (int)$obeRow['obe_id'],
                    'deis_id' => $deisId,
                    'natural_key' => $key !== '' ? $key : null,
                    'match_status' => $status,
                    'matched_by' => $key !== '' ? 'INITIAL_NATURAL_KEY' : null,
                ]);
                $stats['total']++;
                $stats[strtolower($status)]++;
            }
            $result[$entityType] = $stats;
        }

        return $result;
    }

    public function summary(): array
    {
        $rows = $this->deis->query(
            'SELECT entity_type, match_status, COUNT(*) AS total
             FROM obe_entity_mappings GROUP BY entity_type, match_status'
        )->fetchAll();
        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['entity_type']][strtolower($row['match_status'])] = (int)$row['total'];
        }
        return $summary;
    }

    private static function key(mixed $value): string
    {
        return mb_strtoupper(trim((string)$value), 'UTF-8');
    }
}
