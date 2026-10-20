<?php
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Models\AcademicYear;
use App\Services\Obe\AcademicYearProjectionService;
use App\Services\Obe\AcademicYearRepository;
use App\Services\Obe\ObeReadConnection;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum, SUM(is_active) active_total FROM tahun_akademik');
$obeActive = (new AcademicYearRepository($obe))->active();
$projected = (new AcademicYearProjectionService(new AcademicYearRepository($obe)))->active();
$deis = Database::getConnection();
$localActive = $deis->query('SELECT * FROM academic_years WHERE is_active=1')->fetchAll();
$after = $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum, SUM(is_active) active_total FROM tahun_akademik');

$assert($obeActive !== null, 'OBE harus memiliki periode aktif.');
$assert(count($localActive) === 1, 'DEIS harus memiliki tepat satu proyeksi periode aktif.');
$assert($projected['source'] === 'OBE', 'Sumber periode aktif harus OBE.');
$assert($projected['obe_id'] === $obeActive['obe_id'], 'ID OBE periode aktif tidak sama.');
$assert($projected['year'] === $localActive[0]['name'], 'Tahun aktif OBE dan DEIS tidak sama.');
$assert($projected['semester'] === $localActive[0]['semester'], 'Semester aktif OBE dan DEIS tidak sama.');
$assert((new AcademicYear())->getActive()['obe_id'] === $obeActive['obe_id'], 'Model AcademicYear belum menggunakan master OBE.');
$assert($before === $after, 'Proyeksi periode aktif mengubah database OBE.');

$mapping = $deis->prepare("SELECT deis_id, match_status, matched_by FROM obe_entity_mappings WHERE entity_type='academic_year' AND obe_id=:obe_id");
$mapping->execute(['obe_id' => $obeActive['obe_id']]);
$mapped = $mapping->fetch();
$assert((int)($mapped['deis_id'] ?? 0) === (int)$projected['id'], 'Proyeksi belum terhubung ke mapping ID.');
$assert(($mapped['match_status'] ?? '') === 'MAPPED', 'Mapping periode aktif harus berstatus MAPPED.');

$sidebar = file_get_contents(dirname(__DIR__) . '/app/views/components/sidebar.php');
$assert(str_contains($sidebar, 'ObeAcademicYearRepository'), 'Sidebar belum membaca daftar periode dari OBE.');
$assert(!str_contains($sidebar, "'2025/2026 Genap'"), 'Sidebar masih memiliki fallback periode lama.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - active academic year follows OBE as the single master; OBE unchanged.\n";
