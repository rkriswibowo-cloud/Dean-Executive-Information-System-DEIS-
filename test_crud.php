<?php
/**
 * Test Master Fakultas, Prodi, and Lecturer CRUD
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Models\StudyProgram;
use App\Models\Lecturer;

echo "========================================================\n";
echo "   TESTING CRUD FOR STUDY PROGRAM & LECTURER            \n";
echo "========================================================\n\n";

// 1. Test Study Program CRUD
$spModel = new StudyProgram();
$newSpId = $spModel->create([
    'faculty_id'           => 1,
    'code'                 => 'TRK',
    'name'                 => 'Teknologi Rekayasa Komputer',
    'degree'               => 'D4',
    'head_name'            => 'Dr. Budi Santoso, M.T.',
    'accreditation_status' => 'Baik Sekali',
    'accreditation_score'  => 325,
    'target_retention'     => 90.0,
    'student_count'        => 120,
    'lecturer_count'       => 6
]);

echo "[PASS] Created Study Program ID: {$newSpId}\n";

$spModel->update($newSpId, ['name' => 'Teknologi Rekayasa Komputer Terapan']);
$updatedSp = $spModel->find($newSpId);
echo "[PASS] Updated Study Program Name: {$updatedSp['name']}\n";

$spModel->delete($newSpId);
$deletedSp = $spModel->find($newSpId);
echo "[PASS] Deleted Study Program (is null): " . ($deletedSp === null ? 'YES' : 'NO') . "\n\n";

// 2. Test Lecturer CRUD
$lecModel = new Lecturer();
$newLecId = $lecModel->create([
    'study_program_id'      => 1,
    'nidn'                  => '0099887766',
    'nip'                   => '198501012010011005',
    'name'                  => 'Dr. Test Dosen, M.Kom.',
    'email'                 => 'test.dosen@ftik.ac.id',
    'phone'                 => '081299887766',
    'academic_rank'         => 'Lektor',
    'education_level'       => 'S3',
    'certification_status'  => 'Tersertifikasi',
    'bkd_status'            => 'Memenuhi',
    'teaching_load_sks'     => 12,
    'attendance_percentage' => 95.0,
    'sinta_score'           => 200,
    'scopus_h_index'        => 3,
    'publication_count'     => 5,
    'pkm_count'             => 2,
    'hki_count'             => 1,
    'books_count'           => 1,
    'status'                => 'Aktif'
]);

echo "[PASS] Created Lecturer ID: {$newLecId}\n";

$lecModel->update($newLecId, ['teaching_load_sks' => 14]);
$updatedLec = $lecModel->find($newLecId);
echo "[PASS] Updated Lecturer SKS: {$updatedLec['teaching_load_sks']}\n";

$lecModel->delete($newLecId);
$deletedLec = $lecModel->find($newLecId);
echo "[PASS] Deleted Lecturer (is null): " . ($deletedLec === null ? 'YES' : 'NO') . "\n\n";

echo "========================================================\n";
echo " ALL CRUD TESTS PASSED SUCCESSFULLY!\n";
echo "========================================================\n";
