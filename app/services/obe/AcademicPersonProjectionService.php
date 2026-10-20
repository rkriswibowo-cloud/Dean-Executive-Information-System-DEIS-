<?php
namespace App\Services\Obe;

use App\Core\Database;
use PDO;

/**
 * Menggabungkan identitas realtime OBE dengan metrik operasional DEIS.
 * OBE selalu menjadi sumber nama, nomor identitas, email, dan program studi.
 */
final class AcademicPersonProjectionService
{
    private PDO $deis;

    public function __construct(private ?ObeReadConnection $obe = null)
    {
        $this->obe ??= new ObeReadConnection();
        $this->deis = Database::getConnection();
    }

    public function lecturers(?int $obeStudyProgramId = null): array
    {
        $masters = (new LecturerRepository($this->obe))->all($obeStudyProgramId);
        $locals = $this->deis->query('SELECT * FROM lecturers')->fetchAll();
        $index = $this->indexBy($locals, 'nidn');

        return array_map(function (array $master) use ($index): array {
            $local = $index[$this->key($master['nidn'] ?? '')] ?? [];
            return array_merge([
                'id' => null, 'local_id' => null, 'academic_rank' => '-',
                'education_level' => '-', 'certification_status' => 'Belum Tersedia',
                'teaching_load_sks' => 0, 'bkd_status' => 'Dalam Penilaian',
                'attendance_percentage' => 0, 'sinta_score' => 0,
                'publication_count' => 0, 'pkm_count' => 0, 'hki_count' => 0,
                'books_count' => 0, 'status' => 'Aktif',
            ], $local, [
                'local_id' => isset($local['id']) ? (int)$local['id'] : null,
                'obe_id' => $master['obe_id'],
                'name' => $master['name'],
                'nidn' => $master['nidn'] ?: $master['username'],
                'nip' => $master['nuptk'],
                'email' => $master['email'],
                'study_program_id' => $master['study_program_id'],
                'program_name' => $master['study_program_name'] ?: $master['homebase'] ?: '-',
                'source' => 'OBE',
                'is_mapped' => isset($local['id']),
            ]);
        }, $masters);
    }

    public function students(?int $obeStudyProgramId = null): array
    {
        $masters = (new StudentRepository($this->obe))->all($obeStudyProgramId);
        $locals = $this->deis->query('SELECT * FROM students')->fetchAll();
        $index = $this->indexBy($locals, 'nim');

        return array_map(function (array $master) use ($index): array {
            $local = $index[$this->key($master['username'])] ?? [];
            $batch = preg_match('/^(20\d{2})/', $master['username'], $match) ? (int)$match[1] : null;
            return array_merge([
                'id' => null, 'local_id' => null, 'batch_year' => $batch,
                'semester' => '-', 'current_gpa' => 0, 'credits_earned' => 0,
                'attendance_percentage' => 0, 'status' => 'Aktif',
                'risk_status' => 'Normal', 'risk_reason' => null,
                'scholarship' => null, 'organization' => null,
            ], $local, [
                'local_id' => isset($local['id']) ? (int)$local['id'] : null,
                'obe_id' => $master['obe_id'],
                'nim' => $master['username'],
                'name' => $master['name'],
                'email' => $master['email'],
                'study_program_id' => $master['study_program_id'],
                'program_name' => $master['study_program_name'] ?: $master['homebase'] ?: '-',
                'source' => 'OBE',
                'is_mapped' => isset($local['id']),
            ]);
        }, $masters);
    }

    private function indexBy(array $rows, string $column): array
    {
        $index = [];
        foreach ($rows as $row) {
            $key = $this->key($row[$column] ?? '');
            if ($key !== '') {
                $index[$key] = $row;
            }
        }
        return $index;
    }

    private function key(mixed $value): string
    {
        return mb_strtoupper(trim((string)$value), 'UTF-8');
    }
}
