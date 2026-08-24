<?php
use App\Helpers\AuthHelper;
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
use App\Services\NotificationService;
use App\Services\CommandCenterService;
use App\Models\Faculty;

$notifications = NotificationService::getUnread(AuthHelper::id());
$unreadCount = NotificationService::countUnread(AuthHelper::id());
$attention = CommandCenterService::getMyAttention();
$currentRole = AuthHelper::role();
$currentUser = AuthHelper::user();

// Active faculty & context resolution
$facultyModel = new Faculty();
$activeFaculty = $facultyModel->find(1) ?? ['code' => 'FTIK', 'name' => 'Fakultas Teknologi Informasi dan Komunikasi'];
$activeFacultyCode = $activeFaculty['code'] ?? 'DEIS';
$activeFacultyName = $activeFaculty['name'] ?? 'Fakultas';

if ($currentRole === 'dekan') {
    $userContextLabel = 'Dekan ' . $activeFacultyCode . ' (Eksekutif)';
} elseif ($currentRole === 'super_admin') {
    $userContextLabel = 'Super Admin Sistem ' . $activeFacultyCode;
} elseif ($currentRole === 'developer') {
    $userContextLabel = 'Developer & Audit ' . $activeFacultyCode;
} elseif ($currentRole === 'kaprodi') {
    $userContextLabel = !empty($currentUser['program_name']) ? 'Prodi ' . $currentUser['program_name'] : $activeFacultyCode . ' Kaprodi';
} elseif ($currentRole === 'spmi') {
    $userContextLabel = 'GKM / SPMI ' . $activeFacultyCode;
} elseif ($currentRole === 'dosen') {
    $userContextLabel = !empty($currentUser['program_name']) ? 'Dosen ' . $currentUser['program_name'] : 'Dosen ' . $activeFacultyCode;
} elseif ($currentRole === 'operator') {
    $userContextLabel = 'Tata Usaha ' . $activeFacultyCode;
} else {
    $userContextLabel = 'Sistem ' . $activeFacultyCode;
}

// Active URI detection helper
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
function isNavActive($path, $currentPath) {
    if ($path === 'dashboard' && ($currentPath === '/deis' || $currentPath === '/deis/' || $currentPath === '/deis/dashboard' || $currentPath === '/dashboard')) {
        return 'active';
    }
    return (strpos($currentPath, $path) !== false) ? 'active' : '';
}

function isGroupActive(array $paths, $currentPath): bool {
    foreach ($paths as $path) {
        if ($path === 'dashboard' && ($currentPath === '/deis' || $currentPath === '/deis/' || $currentPath === '/deis/dashboard' || $currentPath === '/dashboard')) {
            return true;
        }
        if ($path !== 'dashboard' && strpos($currentPath, $path) !== false) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= CsrfHelper::token() ?>">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> - <?= $appConfig['short_name'] ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg">

    <!-- Color modes script -->
    <script src="<?= $baseUrl ?>/assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') {
            document.documentElement.classList.add('collapsed');
            document.documentElement.classList.remove('expanded');
        } else {
            document.documentElement.classList.remove('collapsed');
            document.documentElement.classList.add('expanded');
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.45.0/tabler-icons.min.css">
    
    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css">

    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.css">

    <!-- Dasher Theme CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/theme.css">

    <!-- DEIS Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/custom-deis.css">
</head>
<body data-base-url="<?= $baseUrl ?>">
    <!-- Sidebar Backdrop for Mobile Screens -->
    <div id="sidebarBackdrop" class="sidebar-backdrop d-none"></div>

    <div>
        <!-- Sidebar -->
        <div id="miniSidebar">
            <!-- Brand Logo -->
            <div class="brand-logo d-flex align-items-center justify-content-between">
                <a class="d-flex align-items-center gap-2 text-decoration-none" href="<?= $baseUrl ?>/dashboard">
                    <img src="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg" alt="DEIS Logo" width="32" height="32" class="flex-shrink-0" />
                    <div class="d-flex flex-column brand-text-wrapper">
                        <span class="fw-bold fs-5 text-dark brand-text"><?= $appConfig['short_name'] ?></span>
                        <span class="text-muted brand-subtitle" style="font-size: 0.65rem; line-height: 1;"><?= htmlspecialchars($activeFacultyCode) ?> Executive System</span>
                    </div>
                </a>
                <button type="button" class="btn btn-sm btn-ghost btn-icon sidebar-close-btn d-lg-none" aria-label="Close Sidebar">
                    <i class="ti ti-x fs-4"></i>
                </button>
            </div>

            <!-- Role Badge Banner in Sidebar -->
            <div class="sidebar-user-box px-3 py-2 border-bottom">
                <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light border sidebar-user-card">
                    <div class="avatar avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden sidebar-user-details">
                        <div class="small fw-semibold text-truncate" style="max-width: 140px;"><?= $currentUser['name'] ?? 'User' ?></div>
                        <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;"><?= strtoupper($currentRole) ?></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links (Accordion Structure) -->
            <div class="px-2 py-2" data-simplebar style="max-height: calc(100vh - 140px);">
                <div class="sidebar-accordion" id="sidebarAccordion">

                    <?php if ($currentRole === 'dekan'): ?>
                        <!-- ================= 1. DEKAN (EXECUTIVE LEADERSHIP) ACCORDION ================= -->
                        
                        <!-- Group 1: Pusat Kendali Eksekutif -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpExec = isGroupActive(['dashboard', 'command-center', 'dashboard/analytics'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpExec ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanExec" aria-expanded="<?= $grpExec ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-crown fs-4 text-warning"></i></span>
                                <span class="text">Pusat Kendali Dekan</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanExec" class="collapse sidebar-accordion-collapse <?= $grpExec ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Eksekutif</a></li>
                                    <li>
                                        <a class="sidebar-subnav-link <?= isNavActive('command-center', $currentPath) ?>" href="<?= $baseUrl ?>/command-center">
                                            Command Center & Radar
                                            <?php if (!empty($attention['total'])): ?>
                                                <span class="badge bg-danger rounded-pill ms-auto px-1.5"><?= $attention['total'] ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">
                                            Persetujuan Dekan
                                            <?php if (!empty($attention['pending_approvals'])): ?>
                                                <span class="badge bg-warning text-dark rounded-pill ms-auto px-1.5"><?= $attention['pending_approvals'] ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic', $currentPath) ?>" href="<?= $baseUrl ?>/strategic">Analitik & Tren Kinerja</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Pengawasan Kinerja Fakultas -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMon = isGroupActive(['lecturers', 'academic', 'students', 'accreditation', 'quality', 'cooperations', 'finances'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMon ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanMon" aria-expanded="<?= $grpMon ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-chart-pie-2 fs-4 text-info"></i></span>
                                <span class="text">Pengawasan Fakultas</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanMon" class="collapse sidebar-accordion-collapse <?= $grpMon ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">SDM & Kepatuhan BKD</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers/kpi', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers/kpi">Ranking KPI Dosen</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Radar Akreditasi Prodi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality', $currentPath) ?>" href="<?= $baseUrl ?>/quality">Penjaminan Mutu SPMI</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Mahasiswa & EWS Kritis</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Kemitraan & Kerja Sama</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('finances', $currentPath) ?>" href="<?= $baseUrl ?>/finances">Evaluasi Anggaran RKA</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Tata Kelola & Laporan Resmi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpGov = isGroupActive(['meetings', 'meetings/rtl', 'reports'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpGov ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanGov" aria-expanded="<?= $grpGov ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-calendar-event fs-4 text-primary"></i></span>
                                <span class="text">Tata Kelola & Laporan</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanGov" class="collapse sidebar-accordion-collapse <?= $grpGov ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Rapat Digital Dekanat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings/rtl', $currentPath) ?>" href="<?= $baseUrl ?>/meetings/rtl">Tracking RTL Rapat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('reports', $currentPath) ?>" href="<?= $baseUrl ?>/reports">Laporan Eksekutif Dekan</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif ($currentRole === 'super_admin'): ?>
                        <!-- ================= 2. SUPER ADMIN (SYSTEM & MASTER DATA) ACCORDION ================= -->
                        
                        <!-- Group 1: Pusat Pengelolaan Sistem -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpSys = isGroupActive(['dashboard', 'users', 'master/academic-years'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpSys ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminSys" aria-expanded="<?= $grpSys ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-adjustments fs-4 text-primary"></i></span>
                                <span class="text">Tata Kelola Sistem</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminSys" class="collapse sidebar-accordion-collapse <?= $grpSys ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Sistem</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('users', $currentPath) ?>" href="<?= $baseUrl ?>/users">Manajemen User & RBAC</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/academic-years', $currentPath) ?>" href="<?= $baseUrl ?>/master/academic-years">Tahun Akademik</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Data Master Universitas & Fakultas -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMaster = isGroupActive(['master/faculties', 'master/study-programs', 'master/lecturers', 'lecturers', 'students', 'strategic/indicators'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMaster ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminMaster" aria-expanded="<?= $grpMaster ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-database fs-4 text-secondary"></i></span>
                                <span class="text">Data Master</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminMaster" class="collapse sidebar-accordion-collapse <?= $grpMaster ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/faculties', $currentPath) ?>" href="<?= $baseUrl ?>/master/faculties">Master Fakultas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/study-programs', $currentPath) ?>" href="<?= $baseUrl ?>/master/study-programs">Master Program Studi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">Master Data Dosen & SDM</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Database Mahasiswa</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic">Master Indikator IKU</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Data Operasional & Arsip -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpOp = isGroupActive(['meetings', 'cooperations', 'finances', 'audit'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpOp ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminOp" aria-expanded="<?= $grpOp ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-folder fs-4 text-warning"></i></span>
                                <span class="text">Operasional & Arsip</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminOp" class="collapse sidebar-accordion-collapse <?= $grpOp ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Arsip Notulensi Rapat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Dokumen Kerja Sama</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('finances', $currentPath) ?>" href="<?= $baseUrl ?>/finances">Pagu & Pos Anggaran</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('audit', $currentPath) ?>" href="<?= $baseUrl ?>/audit">Audit Trail & Log</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif ($currentRole === 'developer'): ?>
                        <!-- ================= 3. DEVELOPER (DIAGNOSTICS & AUDIT) ACCORDION ================= -->
                        
                        <!-- Group 1: Konsol Diagnostik & Health Check -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpDev = isGroupActive(['dashboard', 'audit'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpDev ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDevConsole" aria-expanded="<?= $grpDev ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-code fs-4 text-danger"></i></span>
                                <span class="text">Konsol Pengembang</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDevConsole" class="collapse sidebar-accordion-collapse <?= $grpDev ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Overview Server & DB</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('audit', $currentPath) ?>" href="<?= $baseUrl ?>/audit">Audit Trail & Security Logs</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Arsitektur & Integritas Schema -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpSchema = isGroupActive(['users', 'master/faculties', 'command-center/approvals', 'quality/ami'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpSchema ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDevSchema" aria-expanded="<?= $grpSchema ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-server-2 fs-4 text-info"></i></span>
                                <span class="text">Integritas Schema</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDevSchema" class="collapse sidebar-accordion-collapse <?= $grpSchema ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('users', $currentPath) ?>" href="<?= $baseUrl ?>/users">Matriks Permissions & Roles</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/faculties', $currentPath) ?>" href="<?= $baseUrl ?>/master/faculties">Relasi Master Data</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">Log Transaksi Persetujuan</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/ami', $currentPath) ?>" href="<?= $baseUrl ?>/quality/ami">Log Audit Mutu Internal</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif (AuthHelper::hasRole('kaprodi')): ?>
                        <!-- ================= KAPRODI ACCORDION ================= -->
                        
                        <!-- Group 1: Program Studi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpProdi = isGroupActive(['dashboard', 'academic', 'lecturers', 'students'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpProdi ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accProdi" aria-expanded="<?= $grpProdi ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-school fs-4 text-primary"></i></span>
                                <span class="text">Program Studi</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accProdi" class="collapse sidebar-accordion-collapse <?= $grpProdi ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Prodi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/courses', $currentPath) ?>" href="<?= $baseUrl ?>/academic/courses">Kurikulum & RPS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) ?>" href="<?= $baseUrl ?>/academic">Perkuliahan & Kelas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">Dosen & Beban BKD</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Mahasiswa & EWS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/guidance', $currentPath) ?>" href="<?= $baseUrl ?>/academic/guidance">Bimbingan TA & MBKM</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Mutu & Akreditasi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMutu = isGroupActive(['accreditation', 'quality'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMutu ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accMutuProdi" aria-expanded="<?= $grpMutu ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-certificate fs-4 text-warning"></i></span>
                                <span class="text">Mutu & Akreditasi</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accMutuProdi" class="collapse sidebar-accordion-collapse <?= $grpMutu ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Akreditasi Prodi (LED/LKPS)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/ami', $currentPath) ?>" href="<?= $baseUrl ?>/quality/ami">Audit Mutu Internal (AMI)</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Layanan & Rapat -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpLayanan = isGroupActive(['command-center/approvals', 'meetings'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpLayanan ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accLayananProdi" aria-expanded="<?= $grpLayanan ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-send fs-4 text-info"></i></span>
                                <span class="text">Layanan & Rapat</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accLayananProdi" class="collapse sidebar-accordion-collapse <?= $grpLayanan ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">Pengajuan ke Dekan</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Rapat & Notulensi</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif (AuthHelper::hasRole('spmi')): ?>
                        <!-- ================= GKM / SPMI ACCORDION ================= -->
                        
                        <!-- Group 1: Penjaminan Mutu -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpSpmi = isGroupActive(['quality'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpSpmi ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accSpmiMutu" aria-expanded="<?= $grpSpmi ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-shield-check fs-4 text-primary"></i></span>
                                <span class="text">Penjaminan Mutu</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accSpmiMutu" class="collapse sidebar-accordion-collapse <?= $grpSpmi ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality', $currentPath) ?>" href="<?= $baseUrl ?>/quality">Standar SPMI & PPEPP</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/ami', $currentPath) ?>" href="<?= $baseUrl ?>/quality/ami">Audit Mutu Internal (AMI)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/surveys', $currentPath) ?>" href="<?= $baseUrl ?>/quality/surveys">Survei Kepuasan Stakeholder</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Akreditasi & IKU -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpSpmiStrat = isGroupActive(['accreditation', 'strategic', 'meetings/rtl', 'reports'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpSpmiStrat ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accSpmiStrat" aria-expanded="<?= $grpSpmiStrat ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-certificate fs-4 text-warning"></i></span>
                                <span class="text">Akreditasi & Kinerja</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accSpmiStrat" class="collapse sidebar-accordion-collapse <?= $grpSpmiStrat ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Akreditasi BAN-PT/LAM</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic', $currentPath) ?>" href="<?= $baseUrl ?>/strategic">Indikator IKU Fakultas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings/rtl', $currentPath) ?>" href="<?= $baseUrl ?>/meetings/rtl">Tracking Tindak Lanjut RTL</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('reports', $currentPath) ?>" href="<?= $baseUrl ?>/reports">Laporan Eksekutif Mutu</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif (AuthHelper::hasRole('dosen')): ?>
                        <!-- ================= DOSEN ACCORDION ================= -->
                        
                        <!-- Group 1: Portal Tri Dharma -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpDosen = isGroupActive(['dashboard', 'profile', 'lecturers', 'academic'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpDosen ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDosen" aria-expanded="<?= $grpDosen ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-user-circle fs-4 text-primary"></i></span>
                                <span class="text">Portal Tri Dharma</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDosen" class="collapse sidebar-accordion-collapse <?= $grpDosen ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Dosen</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('profile', $currentPath) ?>" href="<?= $baseUrl ?>/profile">Profil & Tri Dharma</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">Kinerja BKD & SINTA</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) ?>" href="<?= $baseUrl ?>/academic">Kelas & Presensi Kuliah</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/guidance', $currentPath) ?>" href="<?= $baseUrl ?>/academic/guidance">Bimbingan Skripsi/DPA</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Layanan Akademik -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpDosenLay = isGroupActive(['command-center/approvals', 'meetings'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpDosenLay ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDosenLay" aria-expanded="<?= $grpDosenLay ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-send fs-4 text-info"></i></span>
                                <span class="text">Layanan & Rapat</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDosenLay" class="collapse sidebar-accordion-collapse <?= $grpDosenLay ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">Pengajuan Usulan / Riset</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Agenda Rapat Fakultas</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- ================= OPERATOR / TENDIK ACCORDION ================= -->
                        
                        <!-- Group 1: Operasional Perkuliahan -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpOp = isGroupActive(['dashboard', 'academic', 'students'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpOp ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accOp" aria-expanded="<?= $grpOp ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-chalkboard fs-4 text-primary"></i></span>
                                <span class="text">Operasional Akademik</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accOp" class="collapse sidebar-accordion-collapse <?= $grpOp ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Operasional</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) ?>" href="<?= $baseUrl ?>/academic">Kelas Perkuliahan</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/courses', $currentPath) ?>" href="<?= $baseUrl ?>/academic/courses">Kurikulum & Matakuliah</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Database Mahasiswa</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Administrasi Tata Usaha -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpOpAdm = isGroupActive(['meetings', 'cooperations', 'finances'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpOpAdm ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accOpAdm" aria-expanded="<?= $grpOpAdm ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-folder fs-4 text-secondary"></i></span>
                                <span class="text">Administrasi Umum</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accOpAdm" class="collapse sidebar-accordion-collapse <?= $grpOpAdm ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Arsip Rapat Digital</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Dokumen Kerja Sama</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('finances', $currentPath) ?>" href="<?= $baseUrl ?>/finances">Data Anggaran RKA</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="content" class="position-relative h-100">
            <!-- Topbar -->
            <div class="navbar-glass navbar navbar-expand-lg px-3 px-lg-4 py-2 border-bottom">
                <div class="container-fluid px-0 d-flex align-items-center justify-content-between">
                    <!-- Left: Collapse toggle & Brand indicator -->
                    <div class="d-flex align-items-center gap-2 gap-md-3">
                        <button id="sidebarToggleBtn" class="sidebar-toggle btn btn-ghost btn-icon rounded-circle d-flex align-items-center justify-content-center p-2" type="button" aria-label="Menu Navigasi">
                            <i class="ti ti-menu-2 fs-3 text-dark"></i>
                        </button>
                        <div class="d-none d-md-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="ti ti-building me-1"></i> <?= htmlspecialchars($userContextLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="text-muted small">|</span>
                            <span class="text-muted small"><i class="ti ti-calendar me-1"></i> <?= FormatHelper::indonesianDate(date('Y-m-d')) ?></span>
                        </div>
                    </div>

                    <!-- Right: Search, Switch Context, Theme Mode, Notifications, User -->
                    <div class="d-flex align-items-center gap-2">
                        <!-- Global Search ⌘K Trigger -->
                        <button type="button" class="btn btn-outline-secondary border-dashed d-flex align-items-center gap-2 px-3 py-1 rounded-pill" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="ti ti-search fs-5 text-muted"></i>
                            <span class="d-none d-sm-inline small text-muted">Cari Dosen, Rapat, IKU...</span>
                            <span class="badge bg-light text-dark border ms-1 py-1 px-1.5" style="font-size: 0.65rem;">⌘K</span>
                        </button>

                        <!-- Context Switcher for Dekan / Super Admin (PRD Section 5) -->
                        <?php if ($currentUser['role_slug'] === 'dekan' || $currentUser['role_slug'] === 'super_admin'): ?>
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-switch-horizontal text-primary"></i>
                                <span class="small d-none d-lg-inline">Konteks: <strong><?= strtoupper($currentRole) ?></strong></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Beralih Konteks Peran</h6></li>
                                <li>
                                    <form action="<?= $baseUrl ?>/switch-context" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="role_slug" value="dekan">
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 <?= $currentRole === 'dekan' ? 'active' : '' ?>">
                                            <i class="ti ti-crown"></i> Dekan (Executive)
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="<?= $baseUrl ?>/switch-context" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="role_slug" value="super_admin">
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 <?= $currentRole === 'super_admin' ? 'active' : '' ?>">
                                            <i class="ti ti-adjustments"></i> Super Admin (Sistem)
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="<?= $baseUrl ?>/switch-context" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="role_slug" value="developer">
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 <?= $currentRole === 'developer' ? 'active' : '' ?>">
                                            <i class="ti ti-code"></i> Developer (Audit & Logs)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Light/Dark Mode Switcher -->
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-icon rounded-circle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-label="Toggle theme">
                                <i class="ti ti-sun fs-4 theme-icon-active"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="light">
                                        <i class="ti ti-sun"></i> Light
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="dark">
                                        <i class="ti ti-moon-stars"></i> Dark
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="auto">
                                        <i class="ti ti-device-laptop"></i> Auto
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-icon rounded-circle position-relative d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-bell fs-4"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        <?= $unreadCount ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 320px;">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-body-tertiary">
                                    <h6 class="mb-0 fw-bold">Notifikasi Sistem</h6>
                                    <span class="badge bg-primary-subtle text-primary"><?= $unreadCount ?> Baru</span>
                                </div>
                                <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                    <?php if (empty($notifications)): ?>
                                        <div class="text-center py-4 text-muted small">
                                            <i class="ti ti-bell-off fs-3 mb-1 d-block"></i>
                                            Tidak ada notifikasi baru
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $n): ?>
                                            <a href="<?= $baseUrl ?>/<?= $n['target_url'] ?? 'dashboard' ?>" class="list-group-item list-group-item-action py-2 px-3">
                                                <div class="d-flex align-items-start gap-2">
                                                    <i class="ti ti-info-circle text-primary mt-1"></i>
                                                    <div>
                                                        <div class="fw-semibold small text-dark"><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                                        <p class="text-muted mb-0 small" style="font-size: 0.75rem;"><?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?></p>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="p-2 text-center border-top">
                                    <a href="<?= $baseUrl ?>/command-center" class="small text-decoration-none fw-semibold text-primary">Lihat Semua di Command Center</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown ms-1">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm">
                                    <?= strtoupper(substr($currentUser['name'] ?? 'D', 0, 1)) ?>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                <li class="px-3 py-2 border-bottom mb-1">
                                    <div class="fw-bold text-dark"><?= $currentUser['name'] ?? 'User' ?></div>
                                    <div class="small text-muted"><?= $currentUser['email'] ?? '' ?></div>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= $baseUrl ?>/profile">
                                        <i class="ti ti-user"></i> Profil Pengguna
                                    </a>
                                </li>
                                <?php if (AuthHelper::hasRole(['super_admin', 'dekan'])): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= $baseUrl ?>/master/faculties">
                                        <i class="ti ti-settings"></i> Pengaturan Fakultas
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger py-2" href="<?= $baseUrl ?>/logout">
                                        <i class="ti ti-logout"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content Container -->
            <div class="custom-container py-4 px-3 px-lg-4">
                <!-- Flash Messages -->
                <?php if (!empty($flash)): ?>
                    <?php if (isset($flash['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                            <i class="ti ti-circle-check fs-4 me-2"></i>
                            <div><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($flash['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                            <i class="ti ti-alert-triangle fs-4 me-2"></i>
                            <div><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($flash['info'])): ?>
                        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                            <i class="ti ti-info-circle fs-4 me-2"></i>
                            <div><?= htmlspecialchars($flash['info'], ENT_QUOTES, 'UTF-8') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Render Injected View Content -->
                <?= $content ?>
            </div>

            <!-- Footer -->
            <footer class="footer mt-auto py-3 border-top bg-body">
                <div class="container-fluid px-4 d-flex flex-column flex-md-row align-items-center justify-content-between text-muted small">
                    <div>
                        © <?= date('Y') ?> <strong><?= $appConfig['short_name'] ?></strong> - Sistem Informasi Eksekutif Dekan <?= htmlspecialchars($activeFacultyName) ?>.
                    </div>
                    <div>
                        Versi <?= $appConfig['version'] ?> • PHP Native Full MVC
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Floating AI Assistant Button (Phase 7 Foundation) -->
    <button type="button" class="btn btn-primary rounded-circle ai-fab p-3 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#aiModal" title="Tanya AI Assistant Dekan">
        <i class="ti ti-sparkles fs-3"></i>
    </button>

    <!-- AI Assistant Modal / Drawer -->
    <div class="modal fade" id="aiModal" tabindex="-1" aria-labelledby="aiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-xs bg-white text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-sparkles"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fs-6 mb-0 text-white" id="aiModalLabel">Executive AI Assistant</h5>
                            <small class="text-white-50" style="font-size: 0.75rem;">Asisten Cerdas Pengambilan Keputusan Dekan</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-body-tertiary">
                    <div class="ai-chat-box p-3 bg-body rounded-3 border" id="aiChatMessages">
                        <div class="ai-message bot">
                            👋 <strong>Selamat datang di DEIS AI Assistant.</strong><br>
                            Saya dapat menganalisis data fakultas, mengidentifikasi risiko BKD dosen, status akreditasi, hingga capaian IKU. Silakan ajukan pertanyaan atau klik saran di bawah:
                        </div>
                    </div>
                    <!-- Quick Prompt Chips -->
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Ringkas kondisi fakultas'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            📊 Ringkas Kondisi Fakultas
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Siapa dosen yang bermasalah BKD?'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            👨‍🏫 Dosen Bermasalah BKD
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Bagaimana status akreditasi prodi?'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            🏛️ Radar Akreditasi
                        </button>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-body border-top">
                    <form id="aiChatForm" class="d-flex w-100 gap-2">
                        <input type="text" id="aiChatInput" class="form-control" placeholder="Tanya AI seputar data fakultas..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                            <i class="ti ti-send"></i> <span class="d-none d-sm-inline">Kirim</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Search Modal (⌘K Palette) -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="p-3 border-bottom d-flex align-items-center gap-2 bg-body">
                    <i class="ti ti-search fs-4 text-muted"></i>
                    <input type="text" id="globalSearchInput" class="form-control border-0 shadow-none ps-0" placeholder="Ketik untuk mencari Dosen, Mahasiswa, Rapat, RTL, IKU..." autocomplete="off">
                    <span class="badge bg-light text-muted border">ESC to close</span>
                </div>
                <div class="p-3 bg-body-tertiary" id="globalSearchResults" style="max-height: 380px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted">
                        <i class="ti ti-search fs-2 mb-2 d-block"></i>
                        Ketik minimal 2 karakter untuk mencari seluruh data fakultas...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/main.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/app-deis.js"></script>
</body>
</html>
