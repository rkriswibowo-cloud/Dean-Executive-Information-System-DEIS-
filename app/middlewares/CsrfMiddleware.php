<?php
namespace App\Middlewares;

use App\Helpers\CsrfHelper;

class CsrfMiddleware {
    public static function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::verify()) {
                http_response_code(419);
                echo "<div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                        <h1 style='color: #f0ad4e;'>419 - Sesi Kedaluwarsa</h1>
                        <p>Token CSRF tidak valid atau sesi Anda telah berakhir. Silakan muat ulang halaman dan coba kembali.</p>
                        <a href='javascript:history.back()' style='color: #0275d8;'>Kembali & Coba Lagi</a>
                      </div>";
                exit;
            }
        }
    }
}
