<?php
/**
 * DEIS Comprehensive Automated Verification Test Suite
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

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;
use App\Models\User;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\Meeting;
use App\Models\ActionItem;
use App\Models\Indicator;
use App\Models\Approval;
use App\Models\Accreditation;
use App\Models\Cooperation;
use App\Models\Finance;
use App\Services\CommandCenterService;
use App\Helpers\AuthHelper;

echo "========================================================\n";
echo "   DEIS AUTOMATED VERIFICATION TEST SUITE               \n";
echo "========================================================\n\n";

$passed = 0;
$total = 0;

function assertTest($name, $condition) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] {$name}\n";
    } else {
        echo " [FAIL] {$name}\n";
    }
}

try {
    // Test 1: Database Connection
    $db = Database::getConnection();
    assertTest("Database Connection via Singleton PDO", $db instanceof PDO);

    // Test 2: User Model & Auth
    $userModel = new User();
    $dekan = $userModel->findByUsername('dekan');
    assertTest("Find User 'dekan' by Username", $dekan !== null && $dekan['username'] === 'dekan');
    assertTest("Verify Dekan Password Hash", password_verify('password', $dekan['password']));

    // Test 3: Faculty record exists
    $facultyModel = new \App\Models\Faculty();
    $faculty = $facultyModel->find(1);
    assertTest("Faculty record exists (ID: 1)", !empty($faculty) && !empty($faculty['name']));

    $programModel = new StudyProgram();
    $programs = $programModel->allWithFaculty();
    assertTest("Study Programs count >= 3", count($programs) >= 3);

    // Test 4: Lecturers & KPI Ranking
    $lecturerModel = new Lecturer();
    $lecturers = $lecturerModel->allWithProgram();
    assertTest("Lecturers count >= 10", count($lecturers) >= 10);
    $kpiRankings = $lecturerModel->getKpiRanking();
    assertTest("Lecturer KPI Rankings Calculation", count($kpiRankings) > 0 && isset($kpiRankings[0]['kpi_total_score']));

    // Test 5: Students & Early Warning System
    $studentModel = new Student();
    $students = $studentModel->all();
    assertTest("Students count >= 10", count($students) >= 10);
    $earlyWarnings = $studentModel->getEarlyWarningList();
    assertTest("Early Warning at-risk detection", is_array($earlyWarnings));

    // Test 6: Meetings & Digital Packet
    $meetingModel = new Meeting();
    $meetings = $meetingModel->allWithDetails();
    assertTest("Meetings records exist", count($meetings) >= 3);
    $packet = $meetingModel->getDigitalPacket(1);
    assertTest("Digital Meeting Packet structure (Docs, RTL, Participants)", isset($packet['documents']) && isset($packet['action_items']) && isset($packet['participants']));

    // Test 7: Dynamic Indicators (IKU 1-8)
    $indicatorModel = new Indicator();
    $indicators = $indicatorModel->allWithTargetAndRealization(2026);
    assertTest("Dynamic Indicators (IKU) >= 8", count($indicators) >= 8);

    // Test 8: Command Center Service & My Attention
    $attention = CommandCenterService::getMyAttention();
    assertTest("Command Center 'My Attention' total >= 0", isset($attention['total']) && $attention['total'] >= 5);
    $deadlines = CommandCenterService::getDeadlineRadar();
    assertTest("Deadline Radar calculation", count($deadlines) >= 3);
    $alerts = CommandCenterService::getCriticalAlerts();
    assertTest("Critical Alerts Radar count >= 1", count($alerts) >= 1);

    // Test 9: Accreditations & Cooperations
    $accreditationModel = new Accreditation();
    $accreditations = $accreditationModel->allWithProgram();
    assertTest("Accreditation status & countdown records", count($accreditations) >= 3);

    $coopModel = new Cooperation();
    $cooperations = $coopModel->allWithDaysRemaining();
    assertTest("Cooperations with expiration days", count($cooperations) >= 4);

    // Test 10: Finances
    $financeModel = new Finance();
    $finances = $financeModel->allWithProgram(2026);
    assertTest("Finances budget records for 2026", count($finances) >= 5);

    echo "\n========================================================\n";
    echo " RESULTS: {$passed} / {$total} Tests Passed!\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "\n[ERROR] Exception during tests: " . $e->getMessage() . "\n";
}
