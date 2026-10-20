<?php
require __DIR__ . '/bootstrap.php';

use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository;

$controller = file_get_contents(dirname(__DIR__) . '/app/controllers/DashboardController.php');
$view = file_get_contents(dirname(__DIR__) . '/app/views/dashboard/index.php');
$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');
$students = (new StudentRepository($obe))->all();
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$assert(count($students) === 31, 'Baseline total mahasiswa OBE harus 31.');
$assert(str_contains($controller, 'ObeStudentRepository'), 'Dashboard belum memakai repository mahasiswa OBE.');
$assert(!str_contains($controller, "\$studentModel->count(['status' => 'Aktif'])"), 'Dashboard masih menghitung total dari master lokal.');
$assert(str_contains($controller, '$obeStudents'), 'Distribusi mahasiswa belum memakai data OBE.');
$assert(str_contains($view, 'Realtime dari OBE'), 'Sumber angka mahasiswa belum ditampilkan.');
$assert($before === $after, 'Pembacaan dashboard mengubah tabel users OBE.');

if ($failures) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo "OK ({$checks} checks) - dashboard student total and distribution use read-only OBE data.\n";
