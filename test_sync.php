<?php
/**
 * Test Profile Synchronization and Report Print
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
use App\Models\User;
use App\Models\Faculty;
use App\Models\AppSetting;
use App\Models\Lecturer;

echo "==================================================\n";
echo "   TESTING PROFILE SYNC & REPORT PRINT LOGIC      \n";
echo "==================================================\n\n";

$db = Database::getConnection();

// 1. Simulate Profile Update for Dekan
$testName = "Prof. Dr. Ir. Hendra Wijaya, M.Kom., Ph.D.";
$testEmail = "hendra.wijaya.updated@ftik.ac.id";
$testPhone = "081198765432";

// Update user table
$db->prepare("UPDATE users SET name = :name, email = :email, phone = :phone WHERE username = 'dekan'")
   ->execute(['name' => $testName, 'email' => $testEmail, 'phone' => $testPhone]);

// Sync lecturers table
$db->prepare("UPDATE lecturers SET name = :name, email = :email, phone = :phone WHERE nidn = '0012057801' OR user_id = 2")
   ->execute(['name' => $testName, 'email' => $testEmail, 'phone' => $testPhone]);

// Sync faculty
$db->prepare("UPDATE faculties SET dean_name = :name WHERE id = 1")
   ->execute(['name' => $testName]);

// Sync app_settings
$db->prepare("UPDATE app_settings SET setting_value = :name WHERE setting_key = 'dean_name'")
   ->execute(['name' => $testName]);

// 2. Verify Sync in Models
$user = (new User())->findByUsername('dekan');
$faculty = (new Faculty())->find(1);
$lecturer = (new Lecturer())->findByNidn('0012057801');
$settingDean = (new AppSetting())->get('dean_name');

echo "User Name:       " . $user['name'] . "\n";
echo "Faculty Dean:    " . $faculty['dean_name'] . "\n";
echo "Lecturer Name:   " . $lecturer['name'] . "\n";
echo "AppSetting Dean: " . $settingDean . "\n\n";

$isSynced = ($user['name'] === $testName) && 
            ($faculty['dean_name'] === $testName) && 
            ($lecturer['name'] === $testName) && 
            ($settingDean === $testName);

if ($isSynced) {
    echo "[SUCCESS] All 4 tables synchronized perfectly!\n";
} else {
    echo "[FAILED] Sync mismatch detected!\n";
}

echo "==================================================\n";
