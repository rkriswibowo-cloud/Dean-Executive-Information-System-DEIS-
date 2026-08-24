<?php
namespace App\Helpers;

class CsrfHelper {
    public static function generateToken(): string {
        AuthHelper::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function token(): string {
        return self::generateToken();
    }

    public static function tokenField(): string {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(?string $token = null): bool {
        AuthHelper::init();
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
