<?php
require __DIR__ . '/bootstrap.php';

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$topbar = file_get_contents(dirname(__DIR__) . '/app/views/components/topbar.php');
$css = file_get_contents(dirname(__DIR__) . '/public/assets/css/app-theme.css');

$assert(str_contains($topbar, 'brand-text-wrapper'), 'Identitas aplikasi belum mengikuti struktur OBE.');
$assert(str_contains($topbar, 'brand-title">DEIS'), 'Judul brand DEIS belum tampil.');
$assert(str_contains($topbar, 'brand-subtitle-line'), 'Subjudul brand belum tersedia.');
$assert(str_contains($topbar, 'logo-icon.svg'), 'Logo DEIS belum digunakan pada squircle.');
$assert(str_contains($topbar, 'obe-topbar-clock'), 'Jam Jakarta belum tersedia pada header.');
$assert(str_contains($topbar, "timeZone: 'Asia/Jakarta'"), 'Zona waktu header bukan Asia/Jakarta.');
$assert(!str_contains($topbar, 'topbar-divider-slash'), 'Pemisah slash lama masih tampil.');
$assert(str_contains($css, 'height: 64px;'), 'Tinggi topbar belum sama dengan OBE.');
$assert(str_contains($css, '.brand-text-wrapper'), 'CSS brand OBE belum diterapkan.');
$assert(str_contains($css, '.obe-topbar-clock'), 'CSS jam OBE belum diterapkan.');
$assert(str_contains($css, 'color: #ffffff !important;'), 'Teks brand belum dikunci berwarna putih.');
$assert(str_contains($css, 'flex-direction: column !important;'), 'Teks brand belum dikunci menjadi tiga baris vertikal.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - DEIS topbar follows the OBE header structure.\n";
