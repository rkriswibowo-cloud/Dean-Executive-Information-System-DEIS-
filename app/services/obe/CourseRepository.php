<?php
namespace App\Services\Obe;

final class CourseRepository extends BaseObeRepository
{
    public function all(?int $studyProgramId = null, ?int $curriculumId = null, bool $activeOnly = true): array
    {
        $sql = 'SELECT m.id, m.kurikulum_id, k.nama AS kurikulum_nama,
                       k.prodi_id, p.nama AS prodi_nama, m.kode, m.nama,
                       m.sks_teori, m.sks_praktek, m.semester, m.sifat,
                       m.kelompok_keilmuan, m.status
                FROM mata_kuliah m
                JOIN kurikulum k ON k.id = m.kurikulum_id
                JOIN program_studi p ON p.id = k.prodi_id
                WHERE 1 = 1';
        $params = [];
        if ($studyProgramId !== null) {
            $sql .= ' AND k.prodi_id = :prodi_id';
            $params['prodi_id'] = $studyProgramId;
        }
        if ($curriculumId !== null) {
            $sql .= ' AND m.kurikulum_id = :kurikulum_id';
            $params['kurikulum_id'] = $curriculumId;
        }
        if ($activeOnly) {
            $sql .= " AND m.status = 'ACTIVE'";
        }
        $sql .= ' ORDER BY p.nama ASC, m.semester ASC, m.kode ASC';

        return array_map(
            [MasterDataNormalizer::class, 'course'],
            $this->connection->fetchAll($sql, $params)
        );
    }

    public function find(int $obeId): ?array
    {
        $row = $this->connection->fetchOne(
            'SELECT m.id, m.kurikulum_id, k.nama AS kurikulum_nama,
                    k.prodi_id, p.nama AS prodi_nama, m.kode, m.nama,
                    m.sks_teori, m.sks_praktek, m.semester, m.sifat,
                    m.kelompok_keilmuan, m.status
             FROM mata_kuliah m
             JOIN kurikulum k ON k.id = m.kurikulum_id
             JOIN program_studi p ON p.id = k.prodi_id
             WHERE m.id = :id LIMIT 1',
            ['id' => $obeId]
        );
        return $row ? MasterDataNormalizer::course($row) : null;
    }
}
