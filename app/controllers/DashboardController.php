<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\Lecturer;
use App\Models\Meeting;
use App\Models\Indicator;
use App\Models\Finance;
use App\Models\Accreditation;
use App\Services\CommandCenterService;
use App\Services\Obe\ObeReadConnection;
use App\Services\Obe\StudentRepository as ObeStudentRepository;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $studyProgramModel = new StudyProgram();
        $lecturerModel = new Lecturer();
        $meetingModel = new Meeting();
        $indicatorModel = new Indicator();
        $financeModel = new Finance();
        $accreditationModel = new Accreditation();

        // 1. Executive Key Stats
        $programs = $studyProgramModel->allWithFaculty();
        $obeError = null;
        try {
            $totalStudents = count((new ObeStudentRepository(new ObeReadConnection()))->all());
        } catch (\Throwable $e) {
            // Master identitas tidak boleh diam-diam kembali ke data lokal lama.
            $totalStudents = 0;
            $obeError = $e->getMessage();
        }
        $totalLecturers = $lecturerModel->count(['status' => 'Aktif']);
        
        $db = $lecturerModel->getDb();

        // Average GPA
        $stmt = $db->query("SELECT AVG(current_gpa) as avg_gpa FROM students WHERE status = 'Aktif'");
        $avgGpa = round((float)($stmt->fetch()['avg_gpa'] ?? 3.40), 2);

        // BKD Compliance Rate
        $totalActiveLec = $totalLecturers ?: 1;
        $bkdMetCount = $lecturerModel->count(['bkd_status' => 'Memenuhi', 'status' => 'Aktif']);
        $bkdCompliance = round(($bkdMetCount / $totalActiveLec) * 100, 1);

        // Average IKU Achievement
        $stmt = $db->query("SELECT AVG(achievement_percentage) as avg_iku FROM indicator_realizations");
        $avgIkuAchievement = round((float)($stmt->fetch()['avg_iku'] ?? 92.5), 1);

        // Budget Absorption
        $stmt = $db->query("SELECT SUM(budgeted_amount) as total_budget, SUM(realized_amount) as total_realized FROM finances WHERE fiscal_year = 2026");
        $budgetRow = $stmt->fetch();
        $totalBudget = (float)($budgetRow['total_budget'] ?? 1);
        $totalRealized = (float)($budgetRow['total_realized'] ?? 0);
        $budgetAbsorption = $totalBudget > 0 ? round(($totalRealized / $totalBudget) * 100, 1) : 0;

        // 2. Command Center Attention & Critical Alerts
        $attention = CommandCenterService::getMyAttention();
        $criticalAlerts = CommandCenterService::getCriticalAlerts();

        // 3. Upcoming Dean Meetings / Agenda
        $stmt = $db->query("
            SELECT * FROM meetings 
            WHERE meeting_date >= CURDATE() 
            ORDER BY meeting_date ASC, start_time ASC 
            LIMIT 4
        ");
        $upcomingMeetings = $stmt->fetchAll();

        // 4. Strategic Indicators Summary
        $indicators = $indicatorModel->allWithTargetAndRealization(2026);

        // 5. Accreditations Radar
        $accreditations = $accreditationModel->allWithProgram();

        $this->render('dashboard/index', [
            'title'               => 'Executive Dashboard',
            'programs'            => $programs,
            'totalStudents'       => $totalStudents,
            'totalLecturers'      => $totalLecturers,
            'avgGpa'              => $avgGpa,
            'bkdCompliance'       => $bkdCompliance,
            'avgIkuAchievement'   => $avgIkuAchievement,
            'totalBudget'         => $totalBudget,
            'totalRealized'       => $totalRealized,
            'budgetAbsorption'    => $budgetAbsorption,
            'attention'           => $attention,
            'criticalAlerts'      => $criticalAlerts,
            'upcomingMeetings'    => $upcomingMeetings,
            'indicators'          => $indicators,
            'accreditations'      => $accreditations,
            'obeError'            => $obeError
        ]);
    }

    public function analytics(): void {
        $this->requireAuth();

        $this->render('dashboard/analytics', [
            'title' => 'Dashboard Analitik Eksekutif'
        ]);
    }

    public function chartData(): void {
        $this->requireAuth();
        $db = (new StudyProgram())->getDb();

        // Distribusi identitas mahasiswa selalu dihitung realtime dari OBE.
        try {
            $obeStudents = (new ObeStudentRepository(new ObeReadConnection()))->all();
            $studentDistIndex = [];
            foreach ($obeStudents as $student) {
                $name = $student['study_program_name'] ?: $student['homebase'] ?: 'Tanpa Program Studi';
                $key = (string)($student['study_program_id'] ?? $name);
                if (!isset($studentDistIndex[$key])) {
                    $studentDistIndex[$key] = ['code' => $key, 'name' => $name, 'count' => 0];
                }
                $studentDistIndex[$key]['count']++;
            }
            $studentDist = array_values($studentDistIndex);
        } catch (\Throwable $e) {
            $studentDist = [];
        }

        // Lecturer Academic Rank Dist
        $stmt = $db->query("
            SELECT academic_rank, COUNT(*) as count 
            FROM lecturers 
            WHERE status = 'Aktif' 
            GROUP BY academic_rank
        ");
        $rankDist = $stmt->fetchAll();

        // IKU Realization Percentages
        $stmt = $db->query("
            SELECT i.code, ir.achievement_percentage 
            FROM indicators i 
            JOIN indicator_targets it ON i.id = it.indicator_id AND it.year = 2026
            JOIN indicator_realizations ir ON it.id = ir.indicator_target_id
            WHERE i.category = 'IKU'
            ORDER BY i.id ASC
        ");
        $ikuData = $stmt->fetchAll();

        $this->json([
            'status'      => 'success',
            'studentDist' => $studentDist,
            'rankDist'    => $rankDist,
            'ikuData'     => $ikuData
        ]);
    }
}
