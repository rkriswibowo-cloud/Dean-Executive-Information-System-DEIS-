<?php
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Models\Guidance;
use App\Services\Obe\LecturerRepository;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');
$students = (new StudentRepository($obe))->all();
$lecturers = (new LecturerRepository($obe))->all();
$assert(count($students) >= 2, 'OBE harus menyediakan mahasiswa untuk pengujian kelompok.');
$assert(count($lecturers) >= 2, 'OBE harus menyediakan dua dosen pembimbing.');

$model = new Guidance();
$id = $model->createObeCase([
    'type'=>'Skripsi', 'title'=>'TEST CRUD BIMBINGAN OBE', 'progress'=>10,
    'status'=>'Aktif', 'last_date'=>null, 'notes'=>'temporary test'
], [(int)$students[0]['obe_id']], [(int)$lecturers[0]['obe_id'], (int)$lecturers[1]['obe_id']]);

try {
    $rows = $model->allObeCases('Skripsi');
    $created = array_values(array_filter($rows, static fn(array $row): bool => (int)$row['id'] === $id))[0] ?? null;
    $assert($created !== null, 'Data Skripsi gagal dibuat.');
    $assert(count($created['student_ids'] ?? []) === 1, 'Skripsi harus menyimpan satu mahasiswa.');
    $assert(count($created['lecturer_ids'] ?? []) === 2, 'Skripsi harus menyimpan dua pembimbing.');

    $model->updateObeCase($id, [
        'type'=>'KKN', 'title'=>'TEST KELOMPOK KKN', 'progress'=>25,
        'status'=>'Aktif', 'last_date'=>null, 'notes'=>null
    ], [(int)$students[0]['obe_id'], (int)$students[1]['obe_id']], [(int)$lecturers[0]['obe_id']]);
    $updated = array_values(array_filter($model->allObeCases('KKN'), static fn(array $row): bool => (int)$row['id'] === $id))[0] ?? null;
    $assert(count($updated['student_ids'] ?? []) === 2, 'KKN gagal menyimpan lebih dari satu mahasiswa.');
    $assert(count($updated['lecturer_ids'] ?? []) === 1, 'KKN gagal menyimpan satu pembimbing.');
} finally {
    $model->deleteObeCase($id);
}

$db = Database::getConnection();
$assert((int)$db->query("SELECT COUNT(*) FROM guidance_cases WHERE title LIKE 'TEST %'")->fetchColumn() === 0, 'Data uji CRUD tidak terhapus.');
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users');
$assert($before === $after, 'CRUD bimbingan mengubah tabel users OBE.');

$controller = file_get_contents(dirname(__DIR__) . '/app/controllers/AcademicController.php');
$view = file_get_contents(dirname(__DIR__) . '/app/views/academic/guidance.php');
$assert(str_contains($controller, "count(\$studentIds) !== 1"), 'Validasi satu mahasiswa Skripsi belum ada.');
$assert(str_contains($controller, "count(\$lecturerIds) !== 2"), 'Validasi dua pembimbing Skripsi belum ada.');
$assert(substr_count($view, 'data-bs-target="#modalCreateGuidance"') === 1, 'Tombol tambah bimbingan harus tersedia tepat satu kali.');
$assert(str_contains($view, 'guidance-add-btn'), 'Tombol tambah belum memiliki aturan visibilitas khusus.');
$assert(str_contains($view, 'guidance-save-btn'), 'Tombol Simpan belum tersedia di bawah form tambah.');
$assert(str_contains($view, '>Simpan</button>'), 'Label tombol Simpan belum benar.');
$tbodyStart = strpos($view, '<tbody>');
$tbodyEnd = strpos($view, '</tbody>', $tbodyStart ?: 0);
$tbodyMarkup = ($tbodyStart !== false && $tbodyEnd !== false) ? substr($view, $tbodyStart, $tbodyEnd - $tbodyStart) : '';
$assert(!str_contains($tbodyMarkup, '<div class="modal'), 'Modal tidak boleh ditempatkan langsung di dalam tbody.');
$assert(str_contains($controller, "['error' => \$error]"), 'Kesalahan validasi belum memakai flash yang ditampilkan layout.');

if ($failures) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo "OK ({$checks} checks) - OBE guidance CRUD supports thesis and group activities read-only.\n";
