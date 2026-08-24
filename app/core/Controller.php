<?php
namespace App\Core;

use App\Helpers\AuthHelper;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;
use App\Middlewares\CsrfMiddleware;

abstract class Controller {
    protected array $config;

    public function __construct() {
        AuthHelper::init();
        $this->config = require __DIR__ . '/../config/app.php';
    }

    protected function requireAuth(): void {
        AuthMiddleware::handle();
    }

    protected function requireRole($roles): void {
        $allowed = is_array($roles) ? $roles : [$roles];
        RoleMiddleware::handle($allowed);
    }

    protected function requireCsrf(): void {
        CsrfMiddleware::handle();
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void {
        // Automatically inject common variables
        $data['currentUser'] = AuthHelper::user();
        $data['currentRole'] = AuthHelper::role();
        $data['appConfig']   = $this->config;
        $data['baseUrl']     = $this->config['base_url'];
        $data['flash']       = $this->getFlash();

        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path, array $flash = []): void {
        if (!empty($flash)) {
            foreach ($flash as $key => $msg) {
                $this->setFlash($key, $msg);
            }
        }

        $url = (strpos($path, 'http') === 0) ? $path : $this->config['base_url'] . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }

    protected function setFlash(string $type, string $message): void {
        AuthHelper::init();
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(?string $type = null) {
        AuthHelper::init();
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        if ($type !== null) {
            $msg = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $msg;
        }

        $flashes = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flashes;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getPost(?string $key = null, $default = null) {
        if ($key === null) return $_POST;
        return $_POST[$key] ?? $default;
    }

    protected function getQuery(?string $key = null, $default = null) {
        if ($key === null) return $_GET;
        return $_GET[$key] ?? $default;
    }

    protected function sanitize(array $data): array {
        $clean = [];
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $clean[$key] = trim(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'));
            } else {
                $clean[$key] = $val;
            }
        }
        return $clean;
    }
}
