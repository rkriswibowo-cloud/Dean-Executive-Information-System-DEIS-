<?php
namespace App\Helpers;

use App\Core\Database;
use PDO;

class AuthHelper {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        self::init();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::init();
        if (!self::check()) {
            return null;
        }

        if (!isset($_SESSION['user_data'])) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id AND u.status = 'active'
                LIMIT 1
            ");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                unset($user['password']);
                $_SESSION['user_data'] = $user;
            } else {
                self::logout();
                return null;
            }
        }

        return $_SESSION['user_data'];
    }

    public static function id(): ?int {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): string {
        $user = self::user();
        if (!$user) return 'guest';
        return $_SESSION['active_context_role'] ?? $user['role_slug'] ?? 'guest';
    }

    public static function hasRole($roles): bool {
        $currentRole = self::role();
        if ($currentRole === 'super_admin') return true; // Super admin has all roles

        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }
        return $currentRole === $roles;
    }

    public static function permissions(): array {
        self::init();
        if (!self::check()) return [];

        if (!isset($_SESSION['user_permissions'])) {
            $user = self::user();
            if (!$user) return [];

            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT p.slug
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id
            ");
            $stmt->execute(['role_id' => $user['role_id']]);
            $_SESSION['user_permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $_SESSION['user_permissions'];
    }

    public static function can(string $permission): bool {
        if (!self::check()) return false;
        $role = self::role();
        if ($role === 'super_admin' || $role === 'dekan') return true;

        $perms = self::permissions();
        return in_array($permission, $perms, true);
    }

    public static function login(array $user): void {
        self::init();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['active_context_role'] = $user['role_slug'];
        unset($user['password']);
        $_SESSION['user_data'] = $user;
        unset($_SESSION['user_permissions']);

        // Update last login
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
    }

    public static function logout(): void {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function switchContext(string $roleSlug): void {
        self::init();
        $user = self::user();
        if (!$user) return;

        // Allow dekan and super_admin to switch contexts
        if ($user['role_slug'] === 'dekan' || $user['role_slug'] === 'super_admin') {
            $_SESSION['active_context_role'] = $roleSlug;
        }
    }
}
