<?php
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Services\Obe\AcademicReferenceRepository;
use App\Services\Obe\ObeMappingService;
use App\Services\Obe\ObeReadConnection;

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$obe = new ObeReadConnection();
$before = [
    'users' => $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users'),
    'courses' => $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM mata_kuliah'),
];

$result = (new ObeMappingService($obe))->reconcile();
$assert(($result['study_program']['mapped'] ?? 0) === 4, 'Keempat program studi harus terpetakan.');
$assert(($result['academic_year']['mapped'] ?? 0) > 0, 'Sebagian tahun akademik seharusnya terpetakan.');
$assert(($result['course']['conflict'] ?? 0) > 0, 'Kode MK lintas kurikulum harus terdeteksi sebagai konflik, bukan dipaksakan.');
$assert(($result['student']['unmapped'] ?? 0) > 0, 'Mahasiswa tanpa pasangan lokal harus ditandai unmapped.');

$deis = Database::getConnection();
$mappingTotal = (int)$deis->query('SELECT COUNT(*) FROM obe_entity_mappings')->fetchColumn();
$assert($mappingTotal === 196, 'Baseline mapping harus memuat 196 identitas OBE.');
$assert((int)$deis->query("SELECT COUNT(*) FROM obe_entity_mappings WHERE match_status='CONFLICT'")->fetchColumn() > 0, 'Konflik mapping harus tersimpan di DEIS.');

$reference = new AcademicReferenceRepository($obe);
$assert(count($reference->curriculumRules()) === 2, 'Aturan kurikulum harus terbaca.');
$assert(count($reference->graduateProfiles()) === 10, 'Profil lulusan harus terbaca.');
$assert(count($reference->learningOutcomes()) === 21, 'CPL harus terbaca.');
$assert(count($reference->rubrics()) === 11, 'Rubrik harus terbaca.');
$assert(count($reference->counts()) === 11, 'Halaman master harus memuat tepat 11 kategori.');

$after = [
    'users' => $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM users'),
    'courses' => $obe->fetchOne('SELECT COUNT(*) total, COALESCE(SUM(id),0) id_sum FROM mata_kuliah'),
];
$assert($before === $after, 'Fingerprint OBE berubah saat rekonsiliasi.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo "OK ({$checks} checks) - ID mapping and 11 read-only masters are valid.\n";
