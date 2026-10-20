<?php
namespace App\Services\Obe;

/** Daftar identitas akun akademik OBE tanpa pernah membaca password. */
final class UserAccountRepository extends BaseObeRepository
{
    public function all(?string $role = null): array
    {
        $sql = "SELECT u.id, u.username, u.nama_lengkap, u.email, u.nidn, u.nuptk,
                       u.homebase, u.prodi_id, p.nama AS prodi_nama,
                       r.id AS role_id, r.name AS role_nama, u.created_at, u.updated_at
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN program_studi p ON p.id = u.prodi_id";
        $params = [];
        if ($role !== null && $role !== '') {
            $sql .= ' WHERE r.name = :role';
            $params['role'] = $role;
        }
        $sql .= ' ORDER BY r.name ASC, u.nama_lengkap ASC';

        return array_map(static fn(array $row): array => [
            'obe_id' => (int)$row['id'],
            'username' => (string)$row['username'],
            'name' => (string)$row['nama_lengkap'],
            'email' => (string)$row['email'],
            'nidn' => self::nullable($row['nidn'] ?? null),
            'nuptk' => self::nullable($row['nuptk'] ?? null),
            'homebase' => self::nullable($row['homebase'] ?? null),
            'study_program_id' => $row['prodi_id'] !== null ? (int)$row['prodi_id'] : null,
            'study_program_name' => self::nullable($row['prodi_nama'] ?? null),
            'role_id' => (int)$row['role_id'],
            'role_name' => (string)$row['role_nama'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ], $this->connection->fetchAll($sql, $params));
    }

    private static function nullable(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' || $value === '-' ? null : $value;
    }
}
