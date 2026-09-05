<?php
namespace App\Helpers;

use App\Core\Database;
use PDO;

class AuthHelper {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_start();
            } else {
                @session_start();
            }
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
        // Super admin (dan role developer yang dilebur) memiliki akses ke seluruh role
        if ($currentRole === 'super_admin' || $currentRole === 'developer') return true;

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
        // Hanya super_admin (dan role developer) yang memiliki bypass mutlak seluruh permission
        if ($role === 'super_admin' || $role === 'developer') return true;

        $perms = self::permissions();
        return in_array($permission, $perms, true);
    }

    public static function isSuperAdmin(): bool {
        self::init();
        if (!self::check()) return false;
        $user = self::user();
        if (!$user) return false;
        if (($user['role_slug'] ?? '') === 'super_admin' || ($user['role_slug'] ?? '') === 'developer') {
            return true;
        }
        return self::isImpersonating();
    }

    public static function isImpersonating(): bool {
        self::init();
        return !empty($_SESSION['impersonator_id']);
    }

    public static function getImpersonator(): ?array {
        self::init();
        if (!self::isImpersonating()) return null;
        return [
            'id'   => (int)$_SESSION['impersonator_id'],
            'name' => $_SESSION['impersonator_name'] ?? 'Super Admin'
        ];
    }

    public static function impersonate(int $targetUserId): bool {
        self::init();
        if (!self::isSuperAdmin()) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute(['id' => $targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            return false;
        }

        // Simpan impersonator_id jika belum dalam mode impersonasi
        if (!self::isImpersonating()) {
            $currentUser = self::user();
            $_SESSION['impersonator_id'] = (int)$currentUser['id'];
            $_SESSION['impersonator_name'] = $currentUser['name'];
        }

        unset($targetUser['password']);
        $_SESSION['user_id'] = (int)$targetUser['id'];
        $_SESSION['user_data'] = $targetUser;
        $_SESSION['active_context_role'] = $targetUser['role_slug'];
        unset($_SESSION['user_permissions']);

        return true;
    }

    public static function leaveImpersonation(): bool {
        self::init();
        if (!self::isImpersonating()) {
            return false;
        }

        $impersonatorId = (int)$_SESSION['impersonator_id'];
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute(['id' => $impersonatorId]);
        $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$superAdmin) {
            return false;
        }

        unset($superAdmin['password']);
        $_SESSION['user_id'] = (int)$superAdmin['id'];
        $_SESSION['user_data'] = $superAdmin;
        $_SESSION['active_context_role'] = $superAdmin['role_slug'];

        unset($_SESSION['impersonator_id']);
        unset($_SESSION['impersonator_name']);
        unset($_SESSION['user_permissions']);

        return true;
    }

    public static function login(array $user): void {
        self::init();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['active_context_role'] = $user['role_slug'];
        unset($user['password']);
        $_SESSION['user_data'] = $user;
        unset($_SESSION['user_permissions']);
        unset($_SESSION['impersonator_id']);
        unset($_SESSION['impersonator_name']);

        // Update last login
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
    }

    public static function logout(): void {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
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
