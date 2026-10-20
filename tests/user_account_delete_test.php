<?php
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Models\User;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$db = Database::getConnection();
$model = new User();
$superAdminCount = $model->countByRoleSlug('super_admin');
$assert($superAdminCount >= 1, 'Minimal satu superadmin harus tersedia.');

$db->beginTransaction();
try {
    $roleId = (int)$db->query("SELECT id FROM roles WHERE slug='kaprodi' LIMIT 1")->fetchColumn();
    $id = $model->create([
        'role_id' => $roleId,
        'name' => 'Temporary Delete Test',
        'username' => 'delete_test_' . bin2hex(random_bytes(4)),
        'email' => 'delete-test-' . bin2hex(random_bytes(4)) . '@example.test',
        'password' => password_hash('temporary', PASSWORD_DEFAULT),
        'status' => 'inactive',
    ]);
    $assert($model->findWithRole($id) !== null, 'Akun uji harus dapat dibuat.');
    $assert($model->delete($id), 'Model harus dapat menghapus akun DEIS.');
    $assert($model->findWithRole($id) === null, 'Akun uji masih ada setelah dihapus.');
} finally {
    if ($db->inTransaction()) $db->rollBack();
}

$controller = file_get_contents(dirname(__DIR__) . '/app/controllers/UserController.php');
$view = file_get_contents(dirname(__DIR__) . '/app/views/users/index.php');
$routes = file_get_contents(dirname(__DIR__) . '/public/index.php');
$assert(str_contains($controller, 'requireCsrf()'), 'Controller pengguna harus menerapkan CSRF.');
$assert(str_contains($controller, 'Superadmin terakhir tidak dapat dihapus'), 'Proteksi superadmin terakhir belum tersedia.');
$assert(str_contains($controller, 'AuthHelper::id()'), 'Proteksi akun sendiri belum tersedia.');
$assert(str_contains($controller, "unset(\$auditTarget['password'])"), 'Password belum dikeluarkan dari audit penghapusan.');
$assert(str_contains($view, '/users/delete'), 'Tombol hapus belum tersedia pada tampilan.');
$assert(str_contains($routes, "'/users/delete'"), 'Route penghapusan akun belum terdaftar.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - DEIS account deletion is guarded and does not touch OBE.\n";
