<?php
/**
 * Dean Executive Information System (DEIS)
 * Front Controller Entry Point
 */

// Error reporting in development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Autoloader for App\* namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\App;

$app = new App();

// 1. Authentication & Profile Routes
$app->get('/', 'DashboardController@index');
$app->get('/login', 'AuthController@login');
$app->post('/login', 'AuthController@login');
$app->get('/logout', 'AuthController@logout');
$app->get('/profile', 'AuthController@profile');
$app->post('/profile', 'AuthController@profile');
$app->post('/switch-context', 'AuthController@switchContext');
$app->post('/impersonate', 'AuthController@impersonate');
$app->post('/impersonate/leave', 'AuthController@leaveImpersonation');
$app->get('/impersonate/leave', 'AuthController@leaveImpersonation');

// 2. Executive Dashboard & Analytics
$app->get('/dashboard', 'DashboardController@index');
$app->get('/dashboard/analytics', 'DashboardController@analytics');
$app->get('/dashboard/chart-data', 'DashboardController@chartData');

// 3. Command Center & Approvals
$app->get('/command-center', 'CommandCenterController@index');
$app->get('/command-center/approvals', 'CommandCenterController@approvals');
$app->post('/command-center/approvals/create', 'CommandCenterController@createApproval');
$app->post('/command-center/approve', 'CommandCenterController@handleApproval');
$app->post('/command-center/resolve-alert', 'CommandCenterController@resolveAlert');

// 4. Data Master
$app->get('/master/faculties', 'MasterController@faculties');
$app->post('/master/faculties', 'MasterController@updateFaculty');
$app->post('/master/faculties/create', 'MasterController@createFaculty');
$app->post('/master/faculties/update', 'MasterController@updateFaculty');
$app->post('/master/faculties/delete', 'MasterController@deleteFaculty');
$app->get('/master/study-programs', 'MasterController@studyPrograms');
$app->post('/master/study-programs/create', 'MasterController@createStudyProgram');
$app->post('/master/study-programs/update', 'MasterController@updateStudyProgram');
$app->post('/master/study-programs/delete', 'MasterController@deleteStudyProgram');
$app->get('/master/academic-years', 'MasterController@academicYears');
$app->post('/master/academic-years', 'MasterController@academicYears');

// 5. Akademik & Perkuliahan
$app->get('/academic', 'AcademicController@index');
$app->get('/academic/courses', 'AcademicController@courses');
$app->get('/academic/practicum', 'AcademicController@practicum');
$app->post('/academic/practicum/confirm', 'AcademicController@confirmPracticum');
$app->post('/academic/practicum/update', 'AcademicController@updatePracticum');
$app->post('/academic/practicum/create', 'AcademicController@createPracticum');
$app->post('/academic/practicum/delete', 'AcademicController@deletePracticum');
$app->get('/academic/guidance', 'AcademicController@guidance');
$app->post('/academic/action-class', 'AcademicController@actionClass');
$app->post('/academic/action-rps', 'AcademicController@actionRps');
$app->post('/academic/action-guidance', 'AcademicController@actionGuidance');

// 6. SDM & Kinerja Dosen
$app->get('/lecturers', 'LecturerController@index');
$app->post('/lecturers/create', 'LecturerController@create');
$app->post('/lecturers/update', 'LecturerController@update');
$app->post('/lecturers/delete', 'LecturerController@delete');
$app->post('/lecturers/action-bkd', 'LecturerController@actionBkd');
$app->post('/lecturers/action-kpi', 'LecturerController@actionKpi');
$app->get('/lecturers/detail', 'LecturerController@detail');
$app->get('/lecturers/kpi', 'LecturerController@kpi');

// 7. Mahasiswa & Alumni
$app->get('/students', 'StudentController@index');
$app->get('/students/early-warning', 'StudentController@earlyWarning');
$app->get('/students/alumni', 'StudentController@alumni');
$app->post('/students/action', 'StudentController@actionStudent');

// 8. Mutu / SPMI & AMI
$app->get('/quality', 'QualityController@index');
$app->get('/quality/ami', 'QualityController@ami');
$app->get('/quality/surveys', 'QualityController@surveys');

// 9. Akreditasi
$app->get('/accreditation', 'AccreditationController@index');
$app->post('/accreditation/update', 'AccreditationController@updateProgress');

// 10. Kinerja Strategis (IKU & Renstra)
$app->get('/strategic', 'StrategicController@index');
$app->get('/strategic/indicators', 'StrategicController@indicators');
$app->post('/strategic/indicators/create', 'StrategicController@createIndicator');
$app->post('/strategic/indicators/update', 'StrategicController@updateIndicator');
$app->post('/strategic/indicators/delete', 'StrategicController@deleteIndicator');
$app->post('/strategic/realization', 'StrategicController@saveRealization');

// 11. Kerja Sama
$app->get('/cooperations', 'CooperationController@index');
$app->post('/cooperations/create', 'CooperationController@create');
$app->post('/cooperations/update', 'CooperationController@update');
$app->post('/cooperations/delete', 'CooperationController@delete');

// 12. Keuangan & Anggaran
$app->get('/finances', 'FinanceController@index');
$app->post('/finances/create', 'FinanceController@create');
$app->post('/finances/update', 'FinanceController@update');
$app->post('/finances/delete', 'FinanceController@delete');

// 13. Rapat & Tata Kelola Digital
$app->get('/meetings', 'MeetingController@index');
$app->get('/meetings/create', 'MeetingController@create');
$app->post('/meetings/create', 'MeetingController@store');
$app->get('/meetings/detail', 'MeetingController@detail');
$app->post('/meetings/upload-document', 'MeetingController@uploadDocument');
$app->get('/meetings/rtl', 'MeetingController@rtl');
$app->post('/meetings/rtl-update', 'MeetingController@updateRtl');

// 14. Laporan Eksekutif
$app->get('/reports', 'ReportController@index');
$app->get('/reports/export', 'ReportController@export');

// 15. Manajemen User & RBAC
$app->get('/users', 'UserController@index');
$app->post('/users/create', 'UserController@create');
$app->post('/users/toggle-status', 'UserController@toggleStatus');

// 16. Audit Log
$app->get('/audit', 'AuditController@index');

// 17. Search & AI Assistant APIs
$app->get('/search', 'SearchController@search');
$app->post('/ai/chat', 'AiController@chat');

// Run the application
$app->run();
