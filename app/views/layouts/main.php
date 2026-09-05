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
} elseif ($currentRole === 'kaprodi') {
    $userContextLabel = !empty($currentUser['program_name']) ? 'Kaprodi ' . $currentUser['program_name'] : 'Kaprodi ' . $activeFacultyCode;
} elseif ($currentRole === 'super_admin') {
    $userContextLabel = 'Super Admin Sistem ' . $activeFacultyCode;
} elseif ($currentRole === 'developer') {
    $userContextLabel = 'Developer & Audit ' . $activeFacultyCode;
} else {
    $userContextLabel = 'Dekanat ' . $activeFacultyCode;
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

    <div class="app-layout-wrapper">
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
                        
                        <!-- Group 1: Pusat Kendali Dekan (Executive Command) -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpExec = isGroupActive(['dashboard', 'command-center', 'dashboard/analytics', 'strategic', 'strategic/indicators'], $currentPath); ?>
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
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic', $currentPath) && !isNavActive('strategic/indicators', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/strategic">Capaian Realisasi IKU</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic/indicators">Master Indikator IKU (CRUD)</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Akademik, Kurikulum & Mahasiswa -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpAcad = isGroupActive(['academic', 'academic/courses', 'academic/practicum', 'academic/guidance', 'students'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpAcad ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanAcad" aria-expanded="<?= $grpAcad ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-school fs-4 text-primary"></i></span>
                                <span class="text">Akademik & Mahasiswa</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanAcad" class="collapse sidebar-accordion-collapse <?= $grpAcad ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/practicum', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/academic">Perkuliahan & Kelas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/courses', $currentPath) ?>" href="<?= $baseUrl ?>/academic/courses">Kurikulum & Kesiapan RPS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/practicum', $currentPath) ?>" href="<?= $baseUrl ?>/academic/practicum">Cek Modul Praktikum Lab</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/guidance', $currentPath) ?>" href="<?= $baseUrl ?>/academic/guidance">Monitoring Bimbingan (TA/MBKM)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Mahasiswa & EWS Kritis</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Kinerja SDM, Mutu & Kelembagaan -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMon = isGroupActive(['lecturers', 'accreditation', 'quality', 'cooperations', 'finances', 'strategic/indicators'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMon ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanMon" aria-expanded="<?= $grpMon ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-chart-pie-2 fs-4 text-info"></i></span>
                                <span class="text">Kinerja SDM & Mutu</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanMon" class="collapse sidebar-accordion-collapse <?= $grpMon ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) && !isNavActive('lecturers/kpi', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/lecturers">SDM & Kepatuhan BKD</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers/kpi', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers/kpi">Ranking KPI Dosen</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Radar Akreditasi Prodi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality', $currentPath) ?>" href="<?= $baseUrl ?>/quality">Penjaminan Mutu SPMI</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic/indicators">Master Indikator IKU</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Kemitraan & Kerja Sama</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('finances', $currentPath) ?>" href="<?= $baseUrl ?>/finances">Evaluasi Anggaran RKA</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 4: Tata Kelola & Laporan Resmi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpGov = isGroupActive(['meetings', 'meetings/rtl', 'reports'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpGov ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accDekanGov" aria-expanded="<?= $grpGov ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-calendar-event fs-4 text-success"></i></span>
                                <span class="text">Tata Kelola & Laporan</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accDekanGov" class="collapse sidebar-accordion-collapse <?= $grpGov ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) && !isNavActive('meetings/rtl', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/meetings">Rapat Digital Dekanat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings/rtl', $currentPath) ?>" href="<?= $baseUrl ?>/meetings/rtl">Tracking RTL Rapat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('reports', $currentPath) ?>" href="<?= $baseUrl ?>/reports">Laporan Eksekutif Dekan</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php elseif ($currentRole === 'super_admin' || $currentRole === 'developer'): ?>
                        <!-- ================= 2. SUPER ADMIN (AKSES SELURUH MENU & SISTEM) ACCORDION ================= -->
                        
                        <!-- Group 1: Pusat Kendali Eksekutif (Executive Command) -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpExec = isGroupActive(['dashboard', 'command-center', 'dashboard/analytics', 'strategic', 'strategic/indicators'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpExec ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminExec" aria-expanded="<?= $grpExec ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-crown fs-4 text-warning"></i></span>
                                <span class="text">Pusat Kendali Eksekutif</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminExec" class="collapse sidebar-accordion-collapse <?= $grpExec ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
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
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic', $currentPath) && !isNavActive('strategic/indicators', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/strategic">Capaian Realisasi IKU</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic/indicators">Master Indikator IKU (CRUD)</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Akademik, Kurikulum & Mahasiswa -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpAcad = isGroupActive(['academic', 'academic/courses', 'academic/practicum', 'academic/guidance', 'students'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpAcad ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminAcad" aria-expanded="<?= $grpAcad ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-school fs-4 text-primary"></i></span>
                                <span class="text">Akademik & Mahasiswa</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminAcad" class="collapse sidebar-accordion-collapse <?= $grpAcad ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/practicum', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/academic">Perkuliahan & Kelas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/courses', $currentPath) ?>" href="<?= $baseUrl ?>/academic/courses">Kurikulum & Kesiapan RPS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/practicum', $currentPath) ?>" href="<?= $baseUrl ?>/academic/practicum">Cek Modul Praktikum Lab</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/guidance', $currentPath) ?>" href="<?= $baseUrl ?>/academic/guidance">Monitoring Bimbingan (TA/MBKM)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Database Mahasiswa & EWS Kritis</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Kinerja SDM, Mutu & Kelembagaan -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMon = isGroupActive(['lecturers', 'accreditation', 'quality', 'cooperations', 'finances'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMon ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminMon" aria-expanded="<?= $grpMon ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-chart-pie-2 fs-4 text-info"></i></span>
                                <span class="text">Kinerja SDM & Mutu</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminMon" class="collapse sidebar-accordion-collapse <?= $grpMon ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) && !isNavActive('lecturers/kpi', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/lecturers">SDM & Kepatuhan BKD</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers/kpi', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers/kpi">Ranking KPI Dosen</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Radar Akreditasi Prodi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality', $currentPath) && !isNavActive('quality/ami', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/quality">Penjaminan Mutu SPMI</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/ami', $currentPath) ?>" href="<?= $baseUrl ?>/quality/ami">Audit Mutu Internal (AMI)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Kemitraan & Kerja Sama</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('finances', $currentPath) ?>" href="<?= $baseUrl ?>/finances">Evaluasi Anggaran RKA</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 4: Tata Kelola & Laporan Resmi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpGov = isGroupActive(['meetings', 'meetings/rtl', 'reports'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpGov ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminGov" aria-expanded="<?= $grpGov ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-calendar-event fs-4 text-success"></i></span>
                                <span class="text">Tata Kelola & Laporan</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminGov" class="collapse sidebar-accordion-collapse <?= $grpGov ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) && !isNavActive('meetings/rtl', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/meetings">Rapat Digital Dekanat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings/rtl', $currentPath) ?>" href="<?= $baseUrl ?>/meetings/rtl">Tracking RTL Rapat</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('reports', $currentPath) ?>" href="<?= $baseUrl ?>/reports">Laporan Eksekutif Dekan</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 5: Tata Kelola Sistem & Keamanan -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpSys = isGroupActive(['users', 'master/academic-years', 'audit'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpSys ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminSys" aria-expanded="<?= $grpSys ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-adjustments fs-4 text-danger"></i></span>
                                <span class="text">Tata Kelola Sistem</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminSys" class="collapse sidebar-accordion-collapse <?= $grpSys ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('users', $currentPath) ?>" href="<?= $baseUrl ?>/users">Manajemen User & RBAC</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/academic-years', $currentPath) ?>" href="<?= $baseUrl ?>/master/academic-years">Tahun Akademik</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('audit', $currentPath) ?>" href="<?= $baseUrl ?>/audit">Audit Trail & Log Keamanan</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 6: Data Master & Integritas Schema -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMaster = isGroupActive(['master/faculties', 'master/study-programs'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMaster ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accAdminMaster" aria-expanded="<?= $grpMaster ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-database fs-4 text-secondary"></i></span>
                                <span class="text">Data Master & Skema</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accAdminMaster" class="collapse sidebar-accordion-collapse <?= $grpMaster ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/faculties', $currentPath) ?>" href="<?= $baseUrl ?>/master/faculties">Master Fakultas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('master/study-programs', $currentPath) ?>" href="<?= $baseUrl ?>/master/study-programs">Master Program Studi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">Master Data Dosen</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Database Mahasiswa</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic/indicators">Master Indikator IKU</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">Log Transaksi Persetujuan</a></li>
                                </ul>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- ================= 4. KAPRODI (PROGRAM STUDI) ACCORDION ================= -->
                        
                        <!-- Group 1: Program Studi & Akademik -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpProdi = isGroupActive(['dashboard', 'academic', 'academic/courses', 'academic/guidance', 'lecturers', 'students'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpProdi ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accProdi" aria-expanded="<?= $grpProdi ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-school fs-4 text-primary"></i></span>
                                <span class="text">Program Studi</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accProdi" class="collapse sidebar-accordion-collapse <?= $grpProdi ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Dashboard Prodi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/courses', $currentPath) ?>" href="<?= $baseUrl ?>/academic/courses">Kurikulum & RPS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>" href="<?= $baseUrl ?>/academic">Perkuliahan & Kelas</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('lecturers', $currentPath) ?>" href="<?= $baseUrl ?>/lecturers">Dosen & Beban BKD</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('students', $currentPath) ?>" href="<?= $baseUrl ?>/students">Mahasiswa & EWS</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('academic/guidance', $currentPath) ?>" href="<?= $baseUrl ?>/academic/guidance">Bimbingan TA & MBKM</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 2: Mutu & Akreditasi -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpMutu = isGroupActive(['accreditation', 'quality', 'strategic/indicators'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpMutu ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accMutuProdi" aria-expanded="<?= $grpMutu ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-certificate fs-4 text-warning"></i></span>
                                <span class="text">Mutu & Akreditasi</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accMutuProdi" class="collapse sidebar-accordion-collapse <?= $grpMutu ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('accreditation', $currentPath) ?>" href="<?= $baseUrl ?>/accreditation">Akreditasi Prodi (LED/LKPS)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality', $currentPath) ?>" href="<?= $baseUrl ?>/quality">Standar SPMI</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('quality/ami', $currentPath) ?>" href="<?= $baseUrl ?>/quality/ami">Audit Mutu Internal (AMI)</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('strategic/indicators', $currentPath) ?>" href="<?= $baseUrl ?>/strategic/indicators">Indikator IKU Fakultas</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Group 3: Layanan & Tata Kelola -->
                        <div class="sidebar-accordion-item mb-1">
                            <?php $grpLayanan = isGroupActive(['command-center/approvals', 'meetings', 'cooperations'], $currentPath); ?>
                            <button class="sidebar-accordion-toggle <?= $grpLayanan ? 'active' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#accLayananProdi" aria-expanded="<?= $grpLayanan ? 'true' : 'false' ?>">
                                <span class="nav-icon"><i class="ti ti-send fs-4 text-info"></i></span>
                                <span class="text">Layanan & Tata Kelola</span>
                                <i class="ti ti-chevron-down accordion-arrow"></i>
                            </button>
                            <div id="accLayananProdi" class="collapse sidebar-accordion-collapse <?= $grpLayanan ? 'show' : '' ?>" data-bs-parent="#sidebarAccordion">
                                <ul class="sidebar-subnav">
                                    <li><a class="sidebar-subnav-link <?= isNavActive('command-center/approvals', $currentPath) ?>" href="<?= $baseUrl ?>/command-center/approvals">Pengajuan ke Dekan</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('meetings', $currentPath) ?>" href="<?= $baseUrl ?>/meetings">Rapat & Notulensi</a></li>
                                    <li><a class="sidebar-subnav-link <?= isNavActive('cooperations', $currentPath) ?>" href="<?= $baseUrl ?>/cooperations">Kemitraan & Kerja Sama</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="content" class="position-relative d-flex flex-column min-vh-100 w-100">
            <?php if (AuthHelper::isImpersonating()): ?>
                <!-- Impersonation Alert Sticky Banner -->
                <div class="impersonation-banner bg-warning text-dark px-3 px-lg-4 py-2 border-bottom border-warning shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2" style="position: sticky; top: 0; z-index: 1090;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark text-white px-2 py-1"><i class="ti ti-mask me-1"></i> MODE PENYAMARAN</span>
                        <span class="small fw-semibold">
                            Anda sedang mengakses sistem sebagai <strong><?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> 
                            (Peran: <span class="badge bg-dark text-warning border px-2"><?= strtoupper($currentRole) ?></span>).
                        </span>
                    </div>
                    <form action="<?= $baseUrl ?>/impersonate/leave" method="POST" class="d-inline mb-0">
                        <?= CsrfHelper::tokenField() ?>
                        <button type="submit" class="btn btn-sm btn-dark shadow-sm d-flex align-items-center gap-1 py-1 px-3 fw-bold">
                            <i class="ti ti-arrow-back-up"></i> Kembali ke Akun Super Admin
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Topbar -->
            <div class="navbar-glass navbar navbar-expand-lg px-3 px-lg-4 py-2 border-bottom flex-shrink-0">
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

                        <!-- Impersonation Quick Controls & Super Admin Status -->
                        <?php if (AuthHelper::isImpersonating()): ?>
                            <form action="<?= $baseUrl ?>/impersonate/leave" method="POST" class="d-inline">
                                <?= CsrfHelper::tokenField() ?>
                                <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold d-flex align-items-center gap-1 shadow-sm px-2.5 py-1">
                                    <i class="ti ti-arrow-back-up"></i>
                                    <span class="small d-none d-md-inline">Keluar Penyamaran</span>
                                </button>
                            </form>
                        <?php elseif (AuthHelper::isSuperAdmin()): ?>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-shield-check text-primary"></i>
                                    <span class="small d-none d-lg-inline">Hak Akses: <strong>SUPER ADMIN</strong></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><h6 class="dropdown-header">Kontrol Akses Super Admin</h6></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= $baseUrl ?>/users">
                                            <i class="ti ti-mask text-primary"></i> Manajemen Pengguna & Impersonasi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= $baseUrl ?>/audit">
                                            <i class="ti ti-shield-lock text-warning"></i> Audit Trail & Log
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <div class="px-3 py-1 text-muted" style="font-size: 0.725rem; max-width: 220px; line-height: 1.3;">
                                            <i class="ti ti-info-circle me-1 text-primary"></i> Untuk impersonasi user, buka menu <strong>Users</strong> dan klik tombol <em>Impersonate</em> pada user tujuan.
                                        </div>
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
            <div class="custom-container py-4 px-3 px-lg-4 flex-grow-1 main-content-container">
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
            <footer class="footer mt-auto py-3 border-top bg-body flex-shrink-0">
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
