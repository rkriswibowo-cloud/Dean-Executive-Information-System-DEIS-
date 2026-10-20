<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected string $table = 'users';

    public function findByUsername(string $username): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.username = :username LIMIT 1";
        return $this->rawFetchOne($sql, ['username' => $username]);
    }

    public function allWithRole(): array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.id ASC";
        return $this->rawFetch($sql);
    }

    public function findWithRole(int $id): ?array {
        return $this->rawFetchOne(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function countByRoleSlug(string $roleSlug): int {
        $row = $this->rawFetchOne(
            "SELECT COUNT(*) AS total FROM users u
             JOIN roles r ON r.id = u.role_id WHERE r.slug = :role_slug",
            ['role_slug' => $roleSlug]
        );
        return (int)($row['total'] ?? 0);
    }
}
