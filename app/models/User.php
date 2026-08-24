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
}
