<?php
namespace App\Services\Obe;

final class AcademicYearRepository extends BaseObeRepository
{
    public function all(): array
    {
        return array_map(
            [MasterDataNormalizer::class, 'academicYear'],
            $this->connection->fetchAll('SELECT id, nama, is_active FROM tahun_akademik ORDER BY id DESC')
        );
    }

    public function active(): ?array
    {
        $row = $this->connection->fetchOne(
            'SELECT id, nama, is_active FROM tahun_akademik WHERE is_active = 1 ORDER BY id DESC LIMIT 1'
        );
        return $row ? MasterDataNormalizer::academicYear($row) : null;
    }

    public function find(int $obeId): ?array
    {
        $row = $this->connection->fetchOne(
            'SELECT id, nama, is_active FROM tahun_akademik WHERE id = :id LIMIT 1',
            ['id' => $obeId]
        );
        return $row ? MasterDataNormalizer::academicYear($row) : null;
    }
}
