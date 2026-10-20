<?php
$view = file_get_contents(dirname(__DIR__) . '/app/views/master/obe.php');
$css = file_get_contents(dirname(__DIR__) . '/public/assets/css/app-theme.css');
$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$assert(str_contains($view, 'obe-master-hero-body'), 'Card hero OBE belum memiliki kelas kontras khusus.');
$assert(str_contains($view, 'obe-master-tabs'), 'Card navigasi OBE belum memiliki kelas tema khusus.');
$assert(str_contains($view, 'obe-master-content'), 'Card data OBE belum memiliki kelas tema khusus.');
$assert(str_contains($css, '[data-theme="light"] .obe-master-hero .obe-master-hero-body'), 'Override hero mode light belum tersedia.');
$assert(str_contains($css, 'background: linear-gradient(135deg, #312e81, #4f46e5, #2563eb) !important;'), 'Gradien hero belum dilindungi dari override mode light.');
$assert(str_contains($css, 'color: rgba(255, 255, 255, 0.88) !important;'), 'Subtitle hero belum memiliki kontras yang cukup.');
$assert(str_contains($css, 'background: #ffffff !important;'), 'Latar hero mode light belum putih.');
$assert(str_contains($css, 'color: #0f172a !important;'), 'Judul hero mode light belum berwarna gelap.');
$assert(str_contains($css, '.obe-master-content tbody td'), 'Kontras teks tabel mode light belum ditetapkan.');

if ($failures) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo "OK ({$checks} checks) - OBE master cards remain legible in light mode.\n";
