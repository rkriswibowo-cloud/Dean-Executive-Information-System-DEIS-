<?php
namespace App\Middlewares;

use App\Helpers\AuthHelper;

class RoleMiddleware {
    public static function handle(array $allowedRoles): void {
        AuthMiddleware::handle();
        
        if (!AuthHelper::hasRole($allowedRoles)) {
            http_response_code(403);
            echo "<div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                    <h1 style='color: #d9534f;'>403 - Akses Ditolak</h1>
                    <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                    <a href='javascript:history.back()' style='color: #0275d8;'>Kembali ke Halaman Sebelumnya</a>
                  </div>";
            exit;
        }
    }
}
