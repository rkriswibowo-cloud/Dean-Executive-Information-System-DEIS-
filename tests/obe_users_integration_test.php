<?php
require __DIR__ . '/bootstrap.php';

use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\UserAccountRepository;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id), 0) id_sum FROM users');
$users = (new UserAccountRepository($obe))->all();
$lecturers = array_filter($users, static fn(array $u): bool => in_array($u['role_name'], ['Dosen', 'Kaprodi'], true));
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id), 0) id_sum FROM users');

$assert(count($users) === (int)$before['total'], 'Jumlah pengguna repository harus sama dengan tabel users OBE.');
$assert(count($lecturers) > 0, 'Daftar OBE harus memuat dosen atau kaprodi.');
$assert(isset($users[0]['obe_id'], $users[0]['role_name']), 'Identitas dan role OBE harus tersedia.');
$assert(!array_key_exists('password', $users[0]), 'Repository tidak boleh mengekspos password OBE.');
$assert($before === $after, 'Pembacaan pengguna mengubah fingerprint OBE.');

$controller = file_get_contents(dirname(__DIR__) . '/app/controllers/UserController.php');
$view = file_get_contents(dirname(__DIR__) . '/app/views/users/index.php');
$assert(str_contains($controller, 'ObeUserAccountRepository'), 'UserController belum memakai repository OBE.');
$assert(str_contains($view, 'Pengguna Akademik OBE'), 'Halaman users belum menampilkan sumber OBE.');
$assert(str_contains($view, 'Akun Akses DEIS'), 'Pemisahan akun RBAC DEIS belum jelas.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - OBE users and lecturers are read-only and visible in DEIS.\n";
