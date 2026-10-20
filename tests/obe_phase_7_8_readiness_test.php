<?php
require __DIR__ . '/bootstrap.php';

use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\ObeReadinessService;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id), 0) id_sum FROM users');
$report = (new ObeReadinessService($obe))->report();
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id), 0) id_sum FROM users');

$assert(in_array($report['status'], ['ready', 'ready_with_warnings'], true), 'Integrasi tidak boleh memiliki kegagalan kritis.');
$assert(($report['critical_failures'] ?? 1) === 0, 'Readiness report memiliki kegagalan kritis.');
$assert(count($report['checks'] ?? []) === 6, 'Readiness report harus memiliki enam pemeriksaan.');

$byName = [];
foreach ($report['checks'] as $check) $byName[$check['name']] = $check;
$assert(($byName['Koneksi OBE']['status'] ?? null) === 'passed', 'Koneksi OBE harus lulus.');
$assert(($byName['Guard read-only']['status'] ?? null) === 'passed', 'Guard read-only harus lulus.');
$assert(($byName['Sinkronisasi lama']['status'] ?? null) === 'passed', 'Blokir sinkronisasi lama harus lulus.');
$assert(isset($byName['Akun database khusus']), 'Pemeriksaan akun database khusus harus tersedia.');
$assert($before === $after, 'Readiness check mengubah fingerprint users OBE.');

$courseModel = file_get_contents(dirname(__DIR__) . '/app/models/Course.php');
$assert(!str_contains($courseModel, "lecturer_source'] = 'master DEIS'"), 'Fallback dosen lama masih aktif.');
$assert(str_contains($courseModel, "lecturer_source'] = 'OBE tidak tersedia'"), 'Status fail-closed OBE belum diterapkan.');

$routes = file_get_contents(dirname(__DIR__) . '/public/index.php');
$assert(str_contains($routes, "'/master/obe/status'"), 'Route status integrasi belum terdaftar.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - phase 7 fail-closed and phase 8 readiness are valid; OBE unchanged.\n";
