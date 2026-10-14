<?php
/**
 * views/components/topbar.php
 * Topbar Full-Width (56px) - Seragam dengan Desain Sistem OBE
 */
use App\Helpers\AuthHelper;
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
use App\Services\NotificationService;
use App\Services\CommandCenterService;
use App\Models\Faculty;

$notifications = [];
$unreadCount = 0;
try {
    NotificationService::syncLiveAlerts(AuthHelper::id());
    $notifications = NotificationService::getUnread(AuthHelper::id(), 8);
    $unreadCount = NotificationService::countUnread(AuthHelper::id());
} catch (\Throwable $e) {
    $notifications = [];
    $unreadCount = 0;
}

$currentRole = 'dekan';
$currentUser = [];
try {
    $currentRole = AuthHelper::role();
    $currentUser = AuthHelper::user() ?? [];
} catch (\Throwable $e) {
    $currentRole = $_SESSION['role'] ?? 'dekan';
    $currentUser = $_SESSION['user'] ?? ['name' => 'Pengguna', 'email' => ''];
}

// Active faculty & context resolution
$activeFacultyCode = 'DEIS';
$activeFacultyName = 'Fakultas';
try {
    $facultyModel = new Faculty();
    $activeFaculty = $facultyModel->find(1) ?? ['code' => 'FTIK', 'name' => 'Fakultas Teknologi Informasi dan Komunikasi'];
    $activeFacultyCode = $activeFaculty['code'] ?? 'DEIS';
    $activeFacultyName = $activeFaculty['name'] ?? 'Fakultas';
} catch (\Throwable $e) {
    $activeFacultyCode = 'DEIS';
    $activeFacultyName = 'Fakultas';
}

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
?>

<div class="topbar">
    <div class="topbar-left">
        <!-- Sidebar Toggle Collapse / Expand Button -->
        <button type="button" class="topbar-sidebar-toggle-btn" id="sidebarToggleBtn" onclick="window.toggleSidebarCollapse()" title="Ciutkan / Buka Lebar Sidebar (Ctrl+B)" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <!-- Brand Squircle Logo -->
        <a href="<?= $baseUrl ?>/dashboard" class="brand-squircle-link" title="<?= $appConfig['short_name'] ?> - <?= htmlspecialchars($activeFacultyName) ?>">
            <div class="brand-squircle">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </a>

        <span class="topbar-divider-slash">/</span>

        <!-- User / Account Pill Profile Dropdown -->
        <div class="user-pill-dropdown-wrapper" id="userPillWrapper">
            <button type="button" class="user-pill-btn" id="userPillBtn" onclick="window.toggleUserDropdown(event)" title="Informasi Akun & Peran" aria-label="Akun Pengguna">
                <span class="user-avatar-circle">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </span>
                <span class="user-email-text">
                    <?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?>
                </span>
                <span class="user-plan-badge">
                    <?= strtoupper($currentRole) ?>
                </span>
                <i class="fa-solid fa-chevron-down pill-chevron"></i>
            </button>
            <div class="user-pill-menu" id="userPillMenu" style="display: none;">
                <div class="user-pill-menu-header">
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.88rem;">
                        <?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                        <?= htmlspecialchars($currentUser['email'] ?? '') ?>
                    </div>
                    <div style="margin-top: 6px;">
                        <span class="badge bg-primary-subtle text-primary" style="font-size: 0.68rem;">
                            <?= htmlspecialchars($userContextLabel) ?>
                        </span>
                    </div>
                </div>
                <div class="user-pill-menu-divider"></div>
                <a href="<?= $baseUrl ?>/dashboard" class="user-pill-menu-item">
                    <i class="fa-solid fa-chart-line"></i> Dashboard Eksekutif
                </a>
                <a href="<?= $baseUrl ?>/profile" class="user-pill-menu-item">
                    <i class="fa-solid fa-user-circle"></i> Profil Pengguna
                </a>
                <?php if (in_array($currentRole, ['super_admin', 'dekan'])): ?>
                <a href="<?= $baseUrl ?>/master/faculties" class="user-pill-menu-item">
                    <i class="fa-solid fa-building-columns"></i> Pengaturan Fakultas
                </a>
                <?php endif; ?>
                <?php if ($currentRole === 'super_admin' || $currentRole === 'developer'): ?>
                <a href="<?= $baseUrl ?>/users" class="user-pill-menu-item">
                    <i class="fa-solid fa-users-gear"></i> Manajemen User & RBAC
                </a>
                <a href="<?= $baseUrl ?>/audit" class="user-pill-menu-item">
                    <i class="fa-solid fa-shield-halved"></i> Audit Trail & Log Keamanan
                </a>
                <?php endif; ?>
                <div class="user-pill-menu-divider"></div>
                <?php if (!empty($_SESSION['impersonator_id'])): ?>
                    <form action="<?= $baseUrl ?>/impersonate/leave" method="POST" style="margin: 0;">
                        <?= CsrfHelper::tokenField() ?>
                        <button type="submit" class="user-pill-menu-item danger" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Keluar Mode Penyamaran
                        </button>
                    </form>
                    <div class="user-pill-menu-divider"></div>
                <?php endif; ?>
                <a href="<?= $baseUrl ?>/logout" class="user-pill-menu-item danger">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
                </a>
            </div>
        </div>

        <!-- Notification Bell & Dropdown (di sebelah kanan profil) -->
        <div class="notif-dropdown-wrapper" id="notifDropdownWrapper">
            <button type="button" class="notif-bell-btn" id="notifBellBtn" onclick="window.toggleNotifDropdown(event)" title="Notifikasi Sistem (<?= $unreadCount ?> Belum Dibaca)" aria-label="Notifikasi">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notif-badge-count" id="notifBadgeCount"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                    <span class="notif-pulse-dot" id="notifPulseDot"></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown-menu" id="notifDropdownMenu" style="display: none;">
                <div class="notif-header">
                    <div class="notif-header-title">
                        <span>Notifikasi</span>
                        <span class="notif-badge-pill" id="notifPillCount" style="<?= $unreadCount > 0 ? '' : 'display: none;' ?>"><?= $unreadCount ?> Baru</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" id="notifMarkReadBtn" onclick="window.markAllNotifRead(event)" style="background: none; border: none; padding: 0; font-size: 0.72rem; color: var(--text-muted); cursor: pointer; <?= $unreadCount > 0 ? '' : 'display: none;' ?>" title="Tandai semua notifikasi telah dibaca">
                            Tandai Dibaca
                        </button>
                        <span id="notifHeaderDivider" style="color: var(--border); font-size: 0.7rem; <?= $unreadCount > 0 ? '' : 'display: none;' ?>">•</span>
                        <a href="<?= $baseUrl ?>/command-center" style="font-size: 0.72rem; color: var(--primary); font-weight: 600;">
                            Command Center
                        </a>
                    </div>
                </div>

                <div class="notif-list-container" id="notifListContainer">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $n): ?>
                            <?php
                                $type = $n['type'] ?? 'alert';
                                $iconClass = 'fa-circle-info';
                                $typeClass = 'notif-type-info';
                                if ($type === 'alert') {
                                    $iconClass = 'fa-triangle-exclamation';
                                    $typeClass = 'notif-type-danger';
                                } elseif ($type === 'deadline') {
                                    $iconClass = 'fa-calendar-xmark';
                                    $typeClass = 'notif-type-warning';
                                } elseif ($type === 'approval') {
                                    $iconClass = 'fa-file-signature';
                                    $typeClass = 'notif-type-info';
                                } elseif ($type === 'rtl') {
                                    $iconClass = 'fa-list-check';
                                    $typeClass = 'notif-type-warning';
                                }
                                $timeText = !empty($n['created_at']) ? FormatHelper::timeAgo($n['created_at']) : 'Baru saja';
                            ?>
                            <a href="<?= $baseUrl ?>/<?= htmlspecialchars($n['target_url'] ?? 'dashboard') ?>" class="notif-item unread" data-id="<?= (int)$n['id'] ?>" onclick="window.markItemRead(<?= (int)$n['id'] ?>, event)">
                                <div class="notif-icon-box <?= $typeClass ?>">
                                    <i class="fa-solid <?= $iconClass ?>"></i>
                                </div>
                                <div class="notif-content">
                                    <div class="notif-title-row">
                                        <span class="notif-title"><?= htmlspecialchars($n['title']) ?></span>
                                        <span class="notif-unread-dot"></span>
                                    </div>
                                    <div class="notif-desc"><?= htmlspecialchars($n['message']) ?></div>
                                    <div class="notif-time">
                                        <i class="fa-regular fa-clock" style="font-size: 0.68rem; margin-right: 3px;"></i>
                                        <?= htmlspecialchars($timeText) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notif-empty-state" id="notifEmptyState">
                            <i class="fa-regular fa-bell-slash" style="font-size: 1.8rem; opacity: 0.35; margin-bottom: 8px;"></i>
                            <div>Belum ada notifikasi baru</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notif-footer">
                    <a href="<?= $baseUrl ?>/command-center" class="notif-footer-link">
                        <span>Buka Radar Command Center & Notifikasi</span>
                        <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem; margin-left: 4px;"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Global Search Trigger Pill (⌘K) -->
        <button type="button" class="btn btn-outline-secondary border-dashed d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill" data-bs-toggle="modal" data-bs-target="#searchModal" style="font-size: 0.8rem; margin-left: 4px; border: 1px dashed var(--border); background: var(--surface);">
            <i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 0.82rem;"></i>
            <span class="text-muted">Cari Dosen, Rapat, IKU...</span>
            <span class="badge bg-light text-dark border ms-1 py-1 px-1.5" style="font-size: 0.65rem;">⌘K</span>
        </button>
    </div>

    <div class="topbar-right" style="display: flex; align-items: center; gap: 10px;">
        <!-- Impersonation indicator button if currently impersonating -->
        <?php if (!empty($_SESSION['impersonator_id'])): ?>
            <form action="<?= $baseUrl ?>/impersonate/leave" method="POST" class="d-inline mb-0">
                <?= CsrfHelper::tokenField() ?>
                <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold d-flex align-items-center gap-1 shadow-sm px-2.5 py-1" style="border-radius: 8px; font-size: 0.78rem;">
                    <i class="fa-solid fa-mask"></i>
                    <span class="d-none d-sm-inline">Keluar Penyamaran</span>
                </button>
            </form>
        <?php endif; ?>

        <!-- Accent Palette Switcher Dropdown (Gradient / Palet) -->
        <div class="palette-dropdown-wrapper" id="paletteDropdownWrapper">
            <button type="button" id="paletteToggleBtn" class="palette-toggle-btn" onclick="window.togglePaletteDropdown(event)" title="Pilih Tema Palet Warna" aria-label="Palette Selector">
                <span class="palette-dot-preview" id="paletteActiveDot"></span>
                <i class="fa-solid fa-palette" style="font-size: 0.85rem; opacity: 0.85;"></i>
                <span id="paletteActiveName" class="d-none d-sm-inline" style="font-size: 0.82rem; font-weight: 600;">Slate Blue</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; opacity: 0.6; margin-left: 2px;"></i>
            </button>
            <div id="paletteDropdownMenu" class="palette-dropdown-menu" style="display: none;">
                <div class="palette-dropdown-header">Warna Aksen</div>
                <div class="palette-options-list">
                    <button type="button" class="palette-option active" data-pal="slate" onclick="window.applyPalette('slate')">
                        <span class="palette-swatch" style="background: #38bdf8;"></span>
                        <span class="palette-name">Slate Blue (Utama)</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="indigo" onclick="window.applyPalette('indigo')">
                        <span class="palette-swatch" style="background: #818cf8;"></span>
                        <span class="palette-name">Indigo</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="emerald" onclick="window.applyPalette('emerald')">
                        <span class="palette-swatch" style="background: #34d399;"></span>
                        <span class="palette-name">Emerald</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="ocean" onclick="window.applyPalette('ocean')">
                        <span class="palette-swatch" style="background: #0ea5e9;"></span>
                        <span class="palette-name">Ocean Blue</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="violet" onclick="window.applyPalette('violet')">
                        <span class="palette-swatch" style="background: #a78bfa;"></span>
                        <span class="palette-name">Violet</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="amber" onclick="window.applyPalette('amber')">
                        <span class="palette-swatch" style="background: #fbbf24;"></span>
                        <span class="palette-name">Amber</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="rose" onclick="window.applyPalette('rose')">
                        <span class="palette-swatch" style="background: #fb7185;"></span>
                        <span class="palette-name">Rose</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dark / Light Mode Toggle Button -->
        <button type="button" id="themeToggleBtn" class="theme-toggle-btn" onclick="window.toggleTheme()" title="Ganti Mode Gelap / Terang (Dark / Light Mode)" aria-label="Toggle Theme">
            <span class="theme-toggle-icon">
                <i class="fa-solid fa-moon" id="themeToggleIcon"></i>
            </span>
            <span id="themeToggleText" style="font-size: 0.82rem; letter-spacing: 0.3px;">Dark</span>
        </button>
    </div>
</div>
