<?php
namespace App\Middlewares;

use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper;
use Exception;

class AuthMiddleware {
    public static function handle(): void {
        AuthHelper::init();
        if (!AuthHelper::check()) {
            $config = require __DIR__ . '/../config/app.php';
            header('Location: ' . $config['base_url'] . '/login');
            exit;
        }
    }
}
