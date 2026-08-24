<?php
namespace App\Core;

use Exception;

class App {
    private array $routes = [];

    public function get(string $path, string $handler): void {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, string $handler): void {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string {
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }

    public function run(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Detect project base path offset
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);

        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $path = $this->normalizePath($uri);

        // Route matching
        if (isset($this->routes[$method][$path])) {
            $this->dispatch($this->routes[$method][$path]);
            return;
        }

        // Support optional trailing query params or direct matching
        http_response_code(404);
        View::render('errors/404', [
            'requestedPath' => $path
        ], 'layouts/auth');
    }

    private function dispatch(string $handler): void {
        [$controllerName, $action] = explode('@', $handler);
        $fullControllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($fullControllerClass)) {
            throw new Exception("Controller class '{$fullControllerClass}' not found.");
        }

        $controller = new $fullControllerClass();
        if (!method_exists($controller, $action)) {
            throw new Exception("Action method '{$action}' not found in '{$fullControllerClass}'.");
        }

        $controller->$action();
    }
}
