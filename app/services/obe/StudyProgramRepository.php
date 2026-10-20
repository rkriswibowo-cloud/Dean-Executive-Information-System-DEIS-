<?php
namespace App\Services\Obe;

final class StudyProgramRepository extends BaseObeRepository
{
    public function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT id, kode, nama, jenjang, fakultas, nama_kaprodi, nidn_kaprodi, status
                FROM program_studi';
        if ($activeOnly) {
            $sql .= " WHERE status = 'ACTIVE'";
        }
        $sql .= ' ORDER BY nama ASC';

        return array_map(
            [MasterDataNormalizer::class, 'studyProgram'],
            $this->connection->fetchAll($sql)
        );
    }

    public function find(int $obeId): ?array
    {
        $row = $this->connection->fetchOne(
            'SELECT id, kode, nama, jenjang, fakultas, nama_kaprodi, nidn_kaprodi, status
             FROM program_studi WHERE id = :id LIMIT 1',
            ['id' => $obeId]
        );
        return $row ? MasterDataNormalizer::studyProgram($row) : null;
    }
}
