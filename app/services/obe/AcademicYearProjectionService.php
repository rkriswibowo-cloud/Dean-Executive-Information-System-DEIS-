<?php
namespace App\Services\Obe;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Proyeksi teknis periode OBE ke DEIS untuk menjaga FK data operasional.
 * OBE tetap satu-satunya master; tabel DEIS tidak boleh diedit manual.
 */
final class AcademicYearProjectionService
{
    private PDO $deis;

    public function __construct(private ?AcademicYearRepository $repository = null)
    {
        $this->repository ??= new AcademicYearRepository();
        $this->deis = Database::getConnection();
    }

    public function active(): array
    {
        $obeYear = $this->repository->active();
        if (!$obeYear) {
            throw new RuntimeException('OBE belum memiliki tahun akademik aktif.');
        }
        if (empty($obeYear['year']) || !in_array($obeYear['semester'], ['Ganjil', 'Genap'], true)) {
            throw new RuntimeException('Format tahun akademik aktif OBE tidak valid: ' . ($obeYear['label'] ?? '-'));
        }

        [$startDate, $endDate] = $this->periodDates($obeYear['year'], $obeYear['semester']);
        $startedTransaction = !$this->deis->inTransaction();
        if ($startedTransaction) $this->deis->beginTransaction();

        try {
            $find = $this->deis->prepare(
                'SELECT id FROM academic_years WHERE name=:name AND semester=:semester LIMIT 1'
            );
            $find->execute(['name' => $obeYear['year'], 'semester' => $obeYear['semester']]);
            $localId = (int)$find->fetchColumn();

            if ($localId <= 0) {
                $insert = $this->deis->prepare(
                    'INSERT INTO academic_years (name, semester, is_active, start_date, end_date)
                     VALUES (:name, :semester, 0, :start_date, :end_date)'
                );
                $insert->execute([
                    'name' => $obeYear['year'], 'semester' => $obeYear['semester'],
                    'start_date' => $startDate, 'end_date' => $endDate,
                ]);
                $localId = (int)$this->deis->lastInsertId();
            } else {
                $updateDates = $this->deis->prepare(
                    'UPDATE academic_years SET start_date=:start_date, end_date=:end_date WHERE id=:id'
                );
                $updateDates->execute(['start_date' => $startDate, 'end_date' => $endDate, 'id' => $localId]);
            }

            $deactivate = $this->deis->prepare('UPDATE academic_years SET is_active=0 WHERE is_active<>0 AND id<>:id');
            $deactivate->execute(['id' => $localId]);
            $activate = $this->deis->prepare('UPDATE academic_years SET is_active=1 WHERE id=:id AND is_active<>1');
            $activate->execute(['id' => $localId]);

            $mapping = $this->deis->prepare(
                "INSERT INTO obe_entity_mappings
                    (entity_type, obe_id, deis_id, natural_key, match_status, matched_by, last_checked_at)
                 VALUES ('academic_year', :obe_id, :deis_id, :natural_key, 'MAPPED', 'OBE_ACTIVE_PROJECTION', NOW())
                 ON DUPLICATE KEY UPDATE deis_id=VALUES(deis_id), natural_key=VALUES(natural_key),
                    match_status='MAPPED', matched_by='OBE_ACTIVE_PROJECTION', last_checked_at=NOW()"
            );
            $mapping->execute([
                'obe_id' => $obeYear['obe_id'], 'deis_id' => $localId,
                'natural_key' => mb_strtoupper($obeYear['label'], 'UTF-8'),
            ]);

            if ($startedTransaction) $this->deis->commit();
            return array_merge($obeYear, [
                'id' => $localId,
                'name' => $obeYear['year'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'source' => 'OBE',
            ]);
        } catch (Throwable $e) {
            if ($startedTransaction && $this->deis->inTransaction()) $this->deis->rollBack();
            throw $e;
        }
    }

    private function periodDates(string $year, string $semester): array
    {
        [$firstYear, $secondYear] = array_map('intval', explode('/', $year));
        return $semester === 'Ganjil'
            ? [sprintf('%04d-08-01', $firstYear), sprintf('%04d-01-31', $secondYear)]
            : [sprintf('%04d-02-01', $secondYear), sprintf('%04d-07-31', $secondYear)];
    }
}
