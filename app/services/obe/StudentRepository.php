<?php
namespace App\Services\Obe;

final class StudentRepository extends BaseObeRepository
{
    public function all(?int $studyProgramId = null): array
    {
        $sql = "SELECT u.id, u.username, u.nama_lengkap, u.email, u.nidn, u.nuptk,
                       u.homebase, u.prodi_id, p.nama AS prodi_nama
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN program_studi p ON p.id = u.prodi_id
                WHERE r.name = 'Mahasiswa'";
        $params = [];
        if ($studyProgramId !== null) {
            $sql .= ' AND u.prodi_id = :prodi_id';
            $params['prodi_id'] = $studyProgramId;
        }
        $sql .= ' ORDER BY u.nama_lengkap ASC';

        return array_map(
            static fn(array $row): array => MasterDataNormalizer::person($row, 'student'),
            $this->connection->fetchAll($sql, $params)
        );
    }

    public function find(int $obeId): ?array
    {
        $row = $this->connection->fetchOne(
            "SELECT u.id, u.username, u.nama_lengkap, u.email, u.nidn, u.nuptk,
                    u.homebase, u.prodi_id, p.nama AS prodi_nama
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN program_studi p ON p.id = u.prodi_id
             WHERE u.id = :id AND r.name = 'Mahasiswa' LIMIT 1",
            ['id' => $obeId]
        );
        return $row ? MasterDataNormalizer::person($row, 'student') : null;
    }
}
