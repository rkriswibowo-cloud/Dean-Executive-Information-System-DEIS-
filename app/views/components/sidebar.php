<?php
/**
 * views/components/sidebar.php
 * Floating Island Sidebar - Seragam dengan Desain Sistem OBE
 * Isi menu, route, dan otorisasi 100% mengikuti proses bisnis DEIS
 */
use App\Helpers\AuthHelper;
use App\Services\CommandCenterService;
use App\Models\AcademicYear;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$currentRole = 'dekan';
$currentUser = [];
$attention = ['total' => 0, 'pending_approvals' => 0, 'overdue_rtl' => 0, 'problematic_lecturers' => 0, 'at_risk_students' => 0, 'urgent_deadlines' => 0];

try {
    $currentRole = AuthHelper::role();
    $currentUser = AuthHelper::user() ?? [];
    $attention = CommandCenterService::getMyAttention();
} catch (\Throwable $e) {
    $currentRole = $_SESSION['role'] ?? 'dekan';
    $currentUser = $_SESSION['user'] ?? ['name' => 'Pengguna', 'email' => ''];
}

if (!function_exists('isNavActive')) {
    function isNavActive(string $path, string $currentPath): string {
        if ($path === 'dashboard' && ($currentPath === '/deis' || $currentPath === '/deis/' || $currentPath === '/deis/dashboard' || $currentPath === '/dashboard')) {
            return 'active';
        }
        return (strpos($currentPath, $path) !== false) ? 'active' : '';
    }
}

if (!function_exists('isGroupActive')) {
    function isGroupActive(array $paths, string $currentPath): bool {
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
}

// Ambil tahun akademik aktif
$activeYearNama = '2025/2026 Genap';
$listAcademicYears = [];
try {
    $ayModel = new AcademicYear();
    $listAcademicYears = $ayModel->all('id DESC');
    foreach ($listAcademicYears as $ay) {
        if (!empty($ay['is_active'])) {
            $activeYearNama = ($ay['year'] ?? '') . ' ' . ucfirst($ay['semester'] ?? '');
            break;
        }
    }
} catch (\Throwable $e) {
    $activeYearNama = '2025/2026 Genap';
}
?>

<aside class="sidebar" id="appSidebar">
    <!-- Quick Search Menu Box (dengan Shortcut '/') -->
    <div class="sidebar-search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="sidebarSearchInput" placeholder="Cari menu... (Search)" autocomplete="off" spellcheck="false">
        <span class="sidebar-kbd" title="Tekan '/' untuk mencari menu">/</span>
    </div>

    <!-- Semester Aktif Dropdown Selector Pill -->
    <div class="sidebar-branch-pill-wrapper" id="branchPillWrapper">
        <button type="button" class="sidebar-branch-pill" id="branchPillBtn" onclick="window.toggleBranchDropdown(event)" title="Semester Akademik: <?= htmlspecialchars($activeYearNama) ?>">
            <span class="branch-pill-left">
                <i class="fa-solid fa-calendar-check"></i>
                <span id="activeSemesterText"><?= htmlspecialchars($activeYearNama) ?></span>
            </span>
            <i class="fa-solid fa-chevron-down branch-pill-icon"></i>
        </button>
        <div class="branch-dropdown-menu" id="branchDropdownMenu" style="display: none;">
            <div class="branch-dropdown-header">Tahun Akademik & Semester</div>
            <?php if (!empty($listAcademicYears)): ?>
                <?php foreach ($listAcademicYears as $ay): ?>
                    <?php 
                    $ayLabel = ($ay['year'] ?? '') . ' ' . ucfirst($ay['semester'] ?? '');
                    $isActive = !empty($ay['is_active']);
                    ?>
                    <div class="branch-dropdown-item <?= $isActive ? 'active' : '' ?>" onclick="window.switchSemester('<?= htmlspecialchars($ayLabel, ENT_QUOTES, 'UTF-8') ?>')">
                        <?php if ($isActive): ?>
                            <i class="fa-solid fa-check check-mark"></i>
                        <?php else: ?>
                            <i class="fa-regular fa-circle check-placeholder"></i>
                        <?php endif; ?>
                        <div>
                            <div class="branch-item-name"><?= htmlspecialchars($ayLabel) ?></div>
                            <div class="branch-item-sub"><?= $isActive ? 'Semester Berjalan (Aktif)' : 'Semester Akademik' ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="branch-dropdown-item active" onclick="window.switchSemester('<?= htmlspecialchars($activeYearNama, ENT_QUOTES, 'UTF-8') ?>')">
                    <i class="fa-solid fa-check check-mark"></i>
                    <div>
                        <div class="branch-item-name"><?= htmlspecialchars($activeYearNama) ?></div>
                        <div class="branch-item-sub">Semester Berjalan (Aktif)</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scrollable Navigation Area -->
    <div class="sidebar-scrollable" id="sidebarScrollable">

        <?php if ($currentRole === 'dekan'): ?>
            <!-- ================= 1. DEKAN (EXECUTIVE LEADERSHIP) ================= -->
            
            <!-- Group 1: Pusat Kendali Dekan -->
            <?php $grpExec = isGroupActive(['dashboard', 'command-center', 'strategic'], $currentPath); ?>
            <div class="sidebar-group <?= $grpExec ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-crown group-icon" style="color: #f59e0b;"></i>
                        <span>Pusat Kendali Dekan</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">5</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('dashboard', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Dashboard Eksekutif</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('command-center', $currentPath) && !isNavActive('command-center/approvals', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/command-center" class="sidebar-link">
                                <i class="fa-solid fa-tower-broadcast"></i>
                                <span>Command Center & Radar</span>
                                <?php if (!empty($attention['total'])): ?>
                                    <span class="badge bg-danger rounded-pill ms-auto px-1.5" style="font-size: 0.65rem;"><?= $attention['total'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('command-center/approvals', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/command-center/approvals" class="sidebar-link">
                                <i class="fa-solid fa-stamp"></i>
                                <span>Persetujuan Dekan</span>
                                <?php if (!empty($attention['pending_approvals'])): ?>
                                    <span class="badge bg-warning text-dark rounded-pill ms-auto px-1.5" style="font-size: 0.65rem;"><?= $attention['pending_approvals'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('strategic', $currentPath) && !isNavActive('strategic/indicators', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/strategic" class="sidebar-link">
                                <i class="fa-solid fa-bullseye"></i>
                                <span>Capaian Realisasi IKU</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('strategic/indicators', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/strategic/indicators" class="sidebar-link">
                                <i class="fa-solid fa-list-check"></i>
                                <span>Master Indikator IKU</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 2: Akademik & Mahasiswa -->
            <?php $grpAcad = isGroupActive(['academic', 'students'], $currentPath); ?>
            <div class="sidebar-group <?= $grpAcad ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-graduation-cap group-icon" style="color: var(--primary);"></i>
                        <span>Akademik & Mahasiswa</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">5</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/practicum', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/academic" class="sidebar-link">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Perkuliahan & Kelas</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/courses', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/courses" class="sidebar-link">
                                <i class="fa-solid fa-book-bookmark"></i>
                                <span>Kurikulum & Kesiapan RPS</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/practicum', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/practicum" class="sidebar-link">
                                <i class="fa-solid fa-flask"></i>
                                <span>Cek Modul Praktikum Lab</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/guidance', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/guidance" class="sidebar-link">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>Monitoring Bimbingan (TA)</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('students', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/students" class="sidebar-link">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Mahasiswa & EWS Kritis</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 3: Kinerja SDM, Mutu & Kelembagaan -->
            <?php $grpMon = isGroupActive(['lecturers', 'accreditation', 'quality', 'cooperations', 'finances'], $currentPath); ?>
            <div class="sidebar-group <?= $grpMon ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-chart-pie group-icon" style="color: #38bdf8;"></i>
                        <span>Kinerja SDM & Mutu</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">7</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('lecturers', $currentPath) && !isNavActive('lecturers/kpi', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/lecturers" class="sidebar-link">
                                <i class="fa-solid fa-user-check"></i>
                                <span>SDM & Kepatuhan BKD</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('lecturers/kpi', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/lecturers/kpi" class="sidebar-link">
                                <i class="fa-solid fa-medal"></i>
                                <span>Ranking KPI Dosen</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('accreditation', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/accreditation" class="sidebar-link">
                                <i class="fa-solid fa-award"></i>
                                <span>Radar Akreditasi Prodi</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('quality', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/quality" class="sidebar-link">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Penjaminan Mutu SPMI</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('cooperations', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/cooperations" class="sidebar-link">
                                <i class="fa-solid fa-handshake"></i>
                                <span>Kemitraan & Kerja Sama</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('finances', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/finances" class="sidebar-link">
                                <i class="fa-solid fa-wallet"></i>
                                <span>Evaluasi Anggaran RKA</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 4: Tata Kelola & Laporan -->
            <?php $grpGov = isGroupActive(['meetings', 'reports'], $currentPath); ?>
            <div class="sidebar-group <?= $grpGov ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-calendar-days group-icon" style="color: #10b981;"></i>
                        <span>Tata Kelola & Laporan</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">3</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('meetings', $currentPath) && !isNavActive('meetings/rtl', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/meetings" class="sidebar-link">
                                <i class="fa-solid fa-comments"></i>
                                <span>Rapat Digital Dekanat</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('meetings/rtl', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/meetings/rtl" class="sidebar-link">
                                <i class="fa-solid fa-bars-progress"></i>
                                <span>Tracking RTL Rapat</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('reports', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/reports" class="sidebar-link">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Laporan Eksekutif Dekan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        <?php elseif ($currentRole === 'super_admin' || $currentRole === 'developer'): ?>
            <!-- ================= 2. SUPER ADMIN (FULL ACCESS) ================= -->
            
            <!-- Group 1: Pusat Kendali Eksekutif -->
            <?php $grpExec = isGroupActive(['dashboard', 'command-center', 'strategic'], $currentPath); ?>
            <div class="sidebar-group <?= $grpExec ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-crown group-icon" style="color: #f59e0b;"></i>
                        <span>Pusat Kendali Eksekutif</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">5</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('dashboard', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Dashboard Eksekutif</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('command-center', $currentPath) && !isNavActive('command-center/approvals', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/command-center" class="sidebar-link">
                                <i class="fa-solid fa-tower-broadcast"></i>
                                <span>Command Center & Radar</span>
                                <?php if (!empty($attention['total'])): ?>
                                    <span class="badge bg-danger rounded-pill ms-auto px-1.5" style="font-size: 0.65rem;"><?= $attention['total'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('command-center/approvals', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/command-center/approvals" class="sidebar-link">
                                <i class="fa-solid fa-stamp"></i>
                                <span>Persetujuan Dekan</span>
                                <?php if (!empty($attention['pending_approvals'])): ?>
                                    <span class="badge bg-warning text-dark rounded-pill ms-auto px-1.5" style="font-size: 0.65rem;"><?= $attention['pending_approvals'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('strategic', $currentPath) && !isNavActive('strategic/indicators', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/strategic" class="sidebar-link">
                                <i class="fa-solid fa-bullseye"></i>
                                <span>Capaian Realisasi IKU</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('strategic/indicators', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/strategic/indicators" class="sidebar-link">
                                <i class="fa-solid fa-list-check"></i>
                                <span>Master Indikator IKU</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 2: Akademik, Kurikulum & Mahasiswa -->
            <?php $grpAcad = isGroupActive(['academic', 'students'], $currentPath); ?>
            <div class="sidebar-group <?= $grpAcad ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-graduation-cap group-icon" style="color: var(--primary);"></i>
                        <span>Akademik & Mahasiswa</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">5</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/practicum', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/academic" class="sidebar-link">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Perkuliahan & Kelas</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/courses', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/courses" class="sidebar-link">
                                <i class="fa-solid fa-book-bookmark"></i>
                                <span>Kurikulum & Kesiapan RPS</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/practicum', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/practicum" class="sidebar-link">
                                <i class="fa-solid fa-flask"></i>
                                <span>Cek Modul Praktikum Lab</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/guidance', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/guidance" class="sidebar-link">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>Monitoring Bimbingan (TA)</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('students', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/students" class="sidebar-link">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Database Mahasiswa & EWS</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 3: Kinerja SDM, Mutu & Kelembagaan -->
            <?php $grpMon = isGroupActive(['lecturers', 'accreditation', 'quality', 'cooperations', 'finances'], $currentPath); ?>
            <div class="sidebar-group <?= $grpMon ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-chart-pie group-icon" style="color: #38bdf8;"></i>
                        <span>Kinerja SDM & Mutu</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">7</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('lecturers', $currentPath) && !isNavActive('lecturers/kpi', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/lecturers" class="sidebar-link">
                                <i class="fa-solid fa-user-check"></i>
                                <span>SDM & Kepatuhan BKD</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('lecturers/kpi', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/lecturers/kpi" class="sidebar-link">
                                <i class="fa-solid fa-medal"></i>
                                <span>Ranking KPI Dosen</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('accreditation', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/accreditation" class="sidebar-link">
                                <i class="fa-solid fa-award"></i>
                                <span>Radar Akreditasi Prodi</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('quality', $currentPath) && !isNavActive('quality/ami', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/quality" class="sidebar-link">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Penjaminan Mutu SPMI</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('quality/ami', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/quality/ami" class="sidebar-link">
                                <i class="fa-solid fa-clipboard-check"></i>
                                <span>Audit Mutu Internal (AMI)</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('cooperations', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/cooperations" class="sidebar-link">
                                <i class="fa-solid fa-handshake"></i>
                                <span>Kemitraan & Kerja Sama</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('finances', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/finances" class="sidebar-link">
                                <i class="fa-solid fa-wallet"></i>
                                <span>Evaluasi Anggaran RKA</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 4: Tata Kelola & Laporan Resmi -->
            <?php $grpGov = isGroupActive(['meetings', 'reports'], $currentPath); ?>
            <div class="sidebar-group <?= $grpGov ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-calendar-days group-icon" style="color: #10b981;"></i>
                        <span>Tata Kelola & Laporan</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">3</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('meetings', $currentPath) && !isNavActive('meetings/rtl', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/meetings" class="sidebar-link">
                                <i class="fa-solid fa-comments"></i>
                                <span>Rapat Digital Dekanat</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('meetings/rtl', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/meetings/rtl" class="sidebar-link">
                                <i class="fa-solid fa-bars-progress"></i>
                                <span>Tracking RTL Rapat</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('reports', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/reports" class="sidebar-link">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Laporan Eksekutif Dekan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 5: Tata Kelola Sistem & Keamanan -->
            <?php $grpSys = isGroupActive(['users', 'master/academic-years', 'audit'], $currentPath); ?>
            <div class="sidebar-group <?= $grpSys ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-sliders group-icon" style="color: #ef4444;"></i>
                        <span>Tata Kelola Sistem</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">3</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('users', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/users" class="sidebar-link">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>Manajemen User & RBAC</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('master/academic-years', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/master/academic-years" class="sidebar-link">
                                <i class="fa-solid fa-calendar-plus"></i>
                                <span>Tahun Akademik</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('audit', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/audit" class="sidebar-link">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Audit Trail & Log Keamanan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 6: Data Master & Skema -->
            <?php $grpMaster = isGroupActive(['master/faculties', 'master/study-programs'], $currentPath); ?>
            <div class="sidebar-group <?= $grpMaster ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-database group-icon" style="color: #8b5cf6;"></i>
                        <span>Data Master & Skema</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">2</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('master/faculties', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/master/faculties" class="sidebar-link">
                                <i class="fa-solid fa-building-columns"></i>
                                <span>Master Fakultas</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('master/study-programs', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/master/study-programs" class="sidebar-link">
                                <i class="fa-solid fa-sitemap"></i>
                                <span>Master Program Studi</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= 3. KAPRODI (PROGRAM STUDI) ================= -->
            
            <!-- Group 1: Program Studi & Akademik -->
            <?php $grpProdi = isGroupActive(['dashboard', 'academic', 'lecturers', 'students'], $currentPath); ?>
            <div class="sidebar-group <?= $grpProdi ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-graduation-cap group-icon" style="color: var(--primary);"></i>
                        <span>Program Studi</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">6</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('dashboard', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Dashboard Prodi</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/courses', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/courses" class="sidebar-link">
                                <i class="fa-solid fa-book-bookmark"></i>
                                <span>Kurikulum & RPS</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic', $currentPath) && !isNavActive('academic/courses', $currentPath) && !isNavActive('academic/guidance', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/academic" class="sidebar-link">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Perkuliahan & Kelas</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('lecturers', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/lecturers" class="sidebar-link">
                                <i class="fa-solid fa-user-check"></i>
                                <span>Dosen & Beban BKD</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('students', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/students" class="sidebar-link">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Mahasiswa & EWS</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('academic/guidance', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/academic/guidance" class="sidebar-link">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>Bimbingan TA & MBKM</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 2: Mutu & Akreditasi -->
            <?php $grpMutu = isGroupActive(['accreditation', 'quality', 'strategic/indicators'], $currentPath); ?>
            <div class="sidebar-group <?= $grpMutu ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-award group-icon" style="color: #f59e0b;"></i>
                        <span>Mutu & Akreditasi</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">4</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('accreditation', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/accreditation" class="sidebar-link">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Akreditasi Prodi (LED/LKPS)</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('quality', $currentPath) && !isNavActive('quality/ami', $currentPath) ? 'active' : '' ?>">
                            <a href="<?= $baseUrl ?>/quality" class="sidebar-link">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Standar SPMI</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('quality/ami', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/quality/ami" class="sidebar-link">
                                <i class="fa-solid fa-clipboard-check"></i>
                                <span>Audit Mutu Internal (AMI)</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('strategic/indicators', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/strategic/indicators" class="sidebar-link">
                                <i class="fa-solid fa-list-check"></i>
                                <span>Indikator IKU Fakultas</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group 3: Layanan & Tata Kelola -->
            <?php $grpLayanan = isGroupActive(['command-center/approvals', 'meetings', 'cooperations'], $currentPath); ?>
            <div class="sidebar-group <?= $grpLayanan ? 'expanded' : '' ?>">
                <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup(this)">
                    <span class="sidebar-section-title">
                        <i class="fa-solid fa-handshake group-icon" style="color: #38bdf8;"></i>
                        <span>Layanan & Tata Kelola</span>
                    </span>
                    <div class="sidebar-group-meta">
                        <span class="sidebar-group-badge">3</span>
                        <i class="fa-solid fa-chevron-down group-chevron"></i>
                    </div>
                </button>
                <div class="sidebar-group-content">
                    <ul class="sidebar-nav">
                        <li class="sidebar-nav-item <?= isNavActive('command-center/approvals', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/command-center/approvals" class="sidebar-link">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Pengajuan ke Dekan</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('meetings', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/meetings" class="sidebar-link">
                                <i class="fa-solid fa-comments"></i>
                                <span>Rapat & Notulensi</span>
                            </a>
                        </li>
                        <li class="sidebar-nav-item <?= isNavActive('cooperations', $currentPath) ?>">
                            <a href="<?= $baseUrl ?>/cooperations" class="sidebar-link">
                                <i class="fa-solid fa-handshake-angle"></i>
                                <span>Kemitraan & Kerja Sama</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <!-- Sidebar Footer (Collapse Sidebar Trigger) -->
    <div class="sidebar-footer">
        <button type="button" class="sidebar-footer-collapse-btn" onclick="window.toggleSidebarCollapse()" title="Ciutkan Sidebar (Ctrl+B)">
            <i class="fa-solid fa-angles-left collapse-icon" id="sidebarCollapseIcon"></i>
            <span class="collapse-label">Ciutkan Sidebar</span>
        </button>
    </div>
</aside>
