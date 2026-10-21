<?php
require __DIR__ . '/bootstrap.php';

use App\Controllers\AcademicController;
use App\Services\Obe\ObeReadConnection;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
};

$obe = new ObeReadConnection();
$totalObeCourses = (int)$obe->scalar("SELECT COUNT(*) FROM mata_kuliah WHERE status = 'ACTIVE'");

$controller = new AcademicController();
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('getNormalizedObeRpsData');
$method->setAccessible(true);

[$courses, $summary, $prodiStats, $programs, $obeAvailable] = $method->invoke($controller, null, '', '');

$assert($obeAvailable === true, 'Koneksi OBE harus terdeteksi dan aktif.');
$assert(count($courses) === $totalObeCourses, "Jumlah mata kuliah monitoring RPS (" . count($courses) . ") harus persis sama dengan Master OBE ($totalObeCourses).");
$assert($summary['total_courses'] === $totalObeCourses, 'Ringkasan total mata kuliah harus persis sama dengan Master OBE.');
$assert(count($programs) === 4, 'Daftar program studi filter harus memuat 4 prodi Master OBE.');

// Pastikan data dummy lokal non-OBE tidak bocor ke monitoring RPS
$codes = array_column($courses, 'code');
$assert(!in_array('TIF301', $codes, true), 'Mata kuliah dummy TIF301 tidak boleh muncul di monitoring RPS.');
$assert(!in_array('DSF201', $codes, true), 'Mata kuliah dummy DSF201 tidak boleh muncul di monitoring RPS.');
$assert(!in_array('TIF102', $codes, true), 'Mata kuliah dummy TIF102 tidak boleh muncul di monitoring RPS.');

// Pastikan mata kuliah riil OBE ada
$assert(in_array('FEB201', $codes, true), 'Mata kuliah FEB201 (Bisnis Digital) harus ada di monitoring RPS.');
$assert(in_array('RPL304', $codes, true), 'Mata kuliah RPL304 (RPL) harus ada di monitoring RPS.');

// Uji filter prodi Bisnis Digital (obe_prodi_id = 4)
[$bdCourses] = $method->invoke($controller, 4, '', '');
$assert(count($bdCourses) === 1, 'Prodi Bisnis Digital harus memiliki tepat 1 mata kuliah aktif di OBE.');
$assert(($bdCourses[0]['code'] ?? '') === 'FEB201', 'Mata kuliah Bisnis Digital harus FEB201.');

// Uji filter status Lengkap
[$lengkapCourses] = $method->invoke($controller, null, 'Lengkap', '');
$assert(count($lengkapCourses) === 15, 'Mata kuliah dengan status RPS Lengkap di baseline OBE harus berjumlah 15.');

// Uji filter Case Method (IKU-7)
[$cmCourses] = $method->invoke($controller, null, '', '1');
$assert(count($cmCourses) === 4, 'Mata kuliah dengan metode Case Method di baseline OBE harus berjumlah 4.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - academic rps-monitoring strictly follows Master OBE courses.\n";
