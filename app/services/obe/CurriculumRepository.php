<?php
namespace App\Services\Obe;

final class CurriculumRepository extends BaseObeRepository
{
    public function all(?int $studyProgramId = null, bool $activeOnly = false): array
    {
        $sql = 'SELECT k.id, k.prodi_id, p.nama AS prodi_nama, k.kode, k.nama,
                       k.tahun_mulai, k.versi, k.status
                FROM kurikulum k
                JOIN program_studi p ON p.id = k.prodi_id
                WHERE 1 = 1';
        $params = [];
        if ($studyProgramId !== null) {
            $sql .= ' AND k.prodi_id = :prodi_id';
            $params['prodi_id'] = $studyProgramId;
        }
        if ($activeOnly) {
            $sql .= " AND k.status = 'ACTIVE'";
        }
        $sql .= ' ORDER BY k.tahun_mulai DESC, p.nama ASC, k.nama ASC';

        return array_map(
            [MasterDataNormalizer::class, 'curriculum'],
            $this->connection->fetchAll($sql, $params)
        );
    }

    public function find(int $obeId): ?array
    {
        $row = $this->connection->fetchOne(
            'SELECT k.id, k.prodi_id, p.nama AS prodi_nama, k.kode, k.nama,
                    k.tahun_mulai, k.versi, k.status
             FROM kurikulum k JOIN program_studi p ON p.id = k.prodi_id
             WHERE k.id = :id LIMIT 1',
            ['id' => $obeId]
        );
        return $row ? MasterDataNormalizer::curriculum($row) : null;
    }
}
