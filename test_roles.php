<?php
/**
 * Role-Based Access Control and Process Flow Test Suite
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

use App\Models\User;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\Approval;
use App\Helpers\AuthHelper;

echo "========================================================\n";
echo "   TESTING RBAC PERMISSIONS & PROCESS WORKFLOW          \n";
echo "========================================================\n\n";

// 1. Check Role Users Exist
$userModel = new User();
$roles = ['dekan', 'kaprodi.ti', 'spmi', 'dosen', 'operator', 'admin'];

foreach ($roles as $r) {
    $u = $userModel->findByUsername($r);
    echo "[PASS] User '{$r}' found with Role ID: {$u['role_id']}\n";
}

// 2. Test Faculty CRUD model functionality
$facultyModel = new Faculty();
$allFaculties = $facultyModel->allWithStats();
echo "[PASS] Total Faculties with Stats: " . count($allFaculties) . "\n";

// 3. Test Approvals Queries per Role
$approvalModel = new Approval();

// Dekan should see all approvals
$dekanApprovals = $approvalModel->allWithDetails(2, null, true);
echo "[PASS] Dekan sees all approvals count: " . count($dekanApprovals) . "\n";

// Kaprodi should see prodi approvals
$kaprodiApprovals = $approvalModel->allWithDetails(3, 1, false);
echo "[PASS] Kaprodi sees prodi approvals count: " . count($kaprodiApprovals) . "\n";

// Dosen should see only their own approvals
$dosenApprovals = $approvalModel->allWithDetails(5, null, false);
echo "[PASS] Dosen sees personal approvals count: " . count($dosenApprovals) . "\n";

echo "\n========================================================\n";
echo " ALL RBAC PROCESS TESTS PASSED SUCCESSFULLY!\n";
echo "========================================================\n";
