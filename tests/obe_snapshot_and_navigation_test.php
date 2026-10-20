<?php
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\ObeSnapshotService;
use App\Services\ObeSyncService;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$fingerprint = static function () use ($obe): array {
    // Sebelas kategori master memakai beberapa tabel bersama (dosen dan mahasiswa
    // sama-sama berasal dari users). Sertakan juga tabel relasi yang dipakai UI.
    $tables = [
        'tahun_akademik', 'program_studi', 'users', 'roles', 'kurikulum',
        'aturan_kurikulum', 'profil_lulusan', 'cpl', 'mata_kuliah', 'rubrik',
        'rubrik_kriteria', 'rubrik_level', 'mata_kuliah_pengampu', 'rps',
    ];
    $result = [];
    foreach ($tables as $table) {
        $result[$table] = $obe->fetchOne("SELECT COUNT(*) total, COALESCE(SUM(id), 0) id_sum FROM `{$table}`");
    }
    return $result;
};

$before = $fingerprint();
$deis = Database::getConnection();
$deis->beginTransaction();

try {
    $snapshot = (new ObeSnapshotService($obe))->captureAcademicMaster(null);
    $assert($snapshot['id'] > 0, 'Snapshot harus mendapatkan ID DEIS.');
    $assert($snapshot['record_count'] > 0, 'Snapshot harus berisi record master OBE.');
    $assert(strlen($snapshot['checksum']) === 64, 'Checksum snapshot harus berupa SHA-256.');

    $stored = (new ObeSnapshotService($obe))->find($snapshot['id']);
    $assert($stored !== null, 'Snapshot harus dapat dibaca kembali dari DEIS.');
    $assert(($stored['snapshot_type'] ?? null) === 'ACADEMIC_MASTER', 'Jenis snapshot tidak sesuai.');
    $assert(count($stored['payload']['courses'] ?? []) > 0, 'Payload snapshot harus memuat mata kuliah.');
    $assert(array_key_exists('rps_summary', $stored['payload'] ?? []), 'Payload harus memuat ringkasan RPS.');

    $syncBlocked = false;
    try {
        (new ObeSyncService())->syncCoursesFromObe();
    } catch (LogicException) {
        $syncBlocked = true;
    }
    $assert($syncBlocked, 'Sinkronisasi salinan master lama harus diblokir.');

    $sidebar = file_get_contents(dirname(__DIR__) . '/app/views/components/sidebar.php');
    $assert(substr_count($sidebar, 'Referensi Akademik OBE') === 2, 'Menu referensi OBE harus tersedia untuk superadmin dan kaprodi.');
    $assert(!str_contains($sidebar, '>Ranking KPI Dosen<'), 'Shortcut KPI dosen seharusnya disederhanakan dari sidebar.');
    $assert(!str_contains($sidebar, '>Tracking RTL Rapat<'), 'Shortcut RTL rapat seharusnya disederhanakan dari sidebar.');
} finally {
    if ($deis->inTransaction()) $deis->rollBack();
}

$after = $fingerprint();
$assert($before === $after, 'Fingerprint 11 tabel OBE berubah selama snapshot/test.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - navigation simplified, snapshots stored only in DEIS, OBE unchanged.\n";
