<?php
require __DIR__ . '/bootstrap.php';

use App\Services\Obe\AcademicYearRepository;
use App\Services\Obe\CourseRepository;
use App\Services\Obe\CurriculumRepository;
use App\Services\Obe\LecturerRepository;
use App\Services\Obe\ObeHealthService;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository;
use App\Services\Obe\StudyProgramRepository;

$failures = [];
$checks = 0;

$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
};

$connection = new ObeReadConnection();
$beforeFingerprint = $connection->fetchOne(
    'SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS id_sum, COALESCE(MAX(id), 0) AS max_id FROM mata_kuliah'
);

$health = (new ObeHealthService($connection))->check();
$assert($health['available'] === true, 'Health check OBE harus tersedia.');
$assert($health['database'] === 'obe_cpl', 'Health check harus terhubung ke database obe_cpl.');

$years = (new AcademicYearRepository($connection))->all();
$activeYear = (new AcademicYearRepository($connection))->active();
$assert(count($years) > 0, 'Repository tahun akademik harus menghasilkan data.');
$assert($activeYear !== null && $activeYear['is_active'] === true, 'Tahun akademik aktif harus ditemukan.');
$assert(isset($years[0]['obe_id'], $years[0]['name']), 'Kontrak tahun akademik tidak lengkap.');

$programs = (new StudyProgramRepository($connection))->all();
$assert(count($programs) === 4, 'Jumlah program studi aktif OBE di baseline harus 4.');
$assert(isset($programs[0]['obe_id'], $programs[0]['code'], $programs[0]['degree']), 'Kontrak program studi tidak lengkap.');

$lecturers = (new LecturerRepository($connection))->all();
$students = (new StudentRepository($connection))->all();
$assert(count($lecturers) > 0, 'Repository dosen harus menghasilkan data.');
$assert(count($students) === 31, 'Jumlah akun mahasiswa OBE di baseline harus 31.');
$assert(($lecturers[0]['type'] ?? null) === 'lecturer', 'Normalizer dosen harus memberi type lecturer.');
$assert(($students[0]['type'] ?? null) === 'student', 'Normalizer mahasiswa harus memberi type student.');

$curricula = (new CurriculumRepository($connection))->all();
$courses = (new CourseRepository($connection))->all();
$assert(count($curricula) === 4, 'Jumlah kurikulum OBE di baseline harus 4.');
$assert(count($courses) === 147, 'Jumlah mata kuliah aktif OBE di baseline harus 147.');
$assert(isset($courses[0]['obe_id'], $courses[0]['curriculum_id'], $courses[0]['credits']), 'Kontrak mata kuliah tidak lengkap.');

$writeRejected = false;
try {
    $connection->fetchAll("UPDATE mata_kuliah SET nama = nama WHERE id = -1");
} catch (InvalidArgumentException $e) {
    $writeRejected = true;
}
$assert($writeRejected, 'Gateway harus menolak perintah UPDATE sebelum dikirim ke database.');

$outfileRejected = false;
try {
    $connection->fetchAll("SELECT * FROM mata_kuliah INTO OUTFILE 'blocked.txt'");
} catch (InvalidArgumentException $e) {
    $outfileRejected = true;
}
$assert($outfileRejected, 'Gateway harus menolak SELECT INTO OUTFILE.');

$afterFingerprint = $connection->fetchOne(
    'SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS id_sum, COALESCE(MAX(id), 0) AS max_id FROM mata_kuliah'
);
$assert($beforeFingerprint === $afterFingerprint, 'Fingerprint tabel OBE berubah selama pengujian read-only.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - OBE read layer is healthy and write operations are blocked.\n";
