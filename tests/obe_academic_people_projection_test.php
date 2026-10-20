<?php
require __DIR__ . '/bootstrap.php';

use App\Services\Obe\AcademicPersonProjectionService;
use App\Services\Obe\ObeReadConnection;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');
$projection = new AcademicPersonProjectionService($obe);
$lecturers = $projection->lecturers();
$students = $projection->students();
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');

$assert(count($lecturers) > 0, 'Proyeksi dosen OBE kosong.');
$assert(count($students) > 0, 'Proyeksi mahasiswa OBE kosong.');
$assert(($lecturers[0]['source'] ?? null) === 'OBE', 'Identitas dosen bukan bersumber dari OBE.');
$assert(($students[0]['source'] ?? null) === 'OBE', 'Identitas mahasiswa bukan bersumber dari OBE.');
$assert(array_key_exists('is_mapped', $lecturers[0]), 'Status pemetaan dosen tidak tersedia.');
$assert(array_key_exists('risk_status', $students[0]), 'Metrik EWS mahasiswa tidak tersedia.');
$assert($before === $after, 'Proyeksi mengubah tabel users OBE.');

if ($failures) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo "OK ({$checks} checks) - lecturer and student identity is projected read-only from OBE.\n";
