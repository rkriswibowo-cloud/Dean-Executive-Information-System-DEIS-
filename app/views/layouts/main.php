<?php
use App\Helpers\AuthHelper;
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
use App\Services\NotificationService;
use App\Services\CommandCenterService;
use App\Models\Faculty;

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= CsrfHelper::token() ?>">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> - <?= $appConfig['short_name'] ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg">

    <!-- Fonts: Inter (Seragam dengan OBE) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Free (Seragam dengan OBE) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tabler Icons (Kompatibilitas View Internal Modul DEIS) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.45.0/tabler-icons.min.css">
    
    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css">

    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.css">

    <!-- Bootstrap 5 CSS (Sistem Grid, Flexbox, Utilities & Komponen) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- DEIS Design System Theme CSS (Seragam dengan OBE) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/app-theme.css">

    <!-- Script Anti-FOUC: Eksekusi tema, palet, dan status sidebar sebelum DOM selesai render -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
                document.documentElement.classList.remove('dark', 'light');
                document.documentElement.classList.add(savedTheme);

                const savedPalette = localStorage.getItem('accent_palette') || 'slate';
                document.documentElement.setAttribute('data-palette', savedPalette);

                if (localStorage.getItem('sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        })();
    </script>
</head>
<body data-base-url="<?= $baseUrl ?>">
    <div class="app-layout" id="appLayout">
        <!-- Full-width Topbar Header (Seragam OBE) -->
        <?php require __DIR__ . '/../components/topbar.php'; ?>

        <!-- Impersonation Sticky Warning Banner (Jika Aktif) -->
        <?php if (AuthHelper::isImpersonating()): ?>
            <div class="deis-impersonation-bar">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white px-2 py-1"><i class="fa-solid fa-mask me-1"></i> MODE PENYAMARAN</span>
                    <span>
                        Anda sedang mengakses sistem sebagai <strong><?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> 
                        (Peran: <strong><?= strtoupper($currentRole) ?></strong>).
                    </span>
                </div>
                <form action="<?= $baseUrl ?>/impersonate/leave" method="POST" class="d-inline mb-0">
                    <?= CsrfHelper::tokenField() ?>
                    <button type="submit" class="btn btn-sm btn-dark text-white shadow-sm d-flex align-items-center gap-1 py-1 px-3 fw-bold">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Kembali ke Akun Super Admin
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Workspace Container (Floating Islands: Sidebar & Main Content) -->
        <div class="app-container">
            <!-- Floating Sidebar Island Card -->
            <?php require __DIR__ . '/../components/sidebar.php'; ?>

            <!-- Main Content Island Card -->
            <div class="main-content">
                <div class="content-area">
                    <div class="content-body">
                        <!-- Flash Notification Messages -->
                        <?php if (!empty($flash)): ?>
                            <?php if (isset($flash['success'])): 
                                $alert = ['type' => 'success', 'message' => $flash['success'], 'dismissible' => true];
                                require __DIR__ . '/../components/alert.php';
                            endif; ?>
                            <?php if (isset($flash['error'])): 
                                $alert = ['type' => 'danger', 'message' => $flash['error'], 'dismissible' => true];
                                require __DIR__ . '/../components/alert.php';
                            endif; ?>
                            <?php if (isset($flash['info'])): 
                                $alert = ['type' => 'info', 'message' => $flash['info'], 'dismissible' => true];
                                require __DIR__ . '/../components/alert.php';
                            endif; ?>
                        <?php endif; ?>

                        <!-- Render Injected View Content -->
                        <?= $content ?>
                    </div>

                    <!-- Minimalist Modern Footer -->
                    <?php require __DIR__ . '/../components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating AI Assistant Button (Asisten Cerdas Dekan) -->
    <button type="button" class="ai-fab" data-bs-toggle="modal" data-bs-target="#aiModal" title="Tanya AI Assistant Dekan" aria-label="Tanya AI">
        <i class="fa-solid fa-wand-magic-sparkles fs-5"></i>
    </button>

    <!-- AI Assistant Modal / Drawer -->
    <div class="modal fade" id="aiModal" tabindex="-1" aria-labelledby="aiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white" style="border-radius: 14px 14px 0 0;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-xs bg-white text-primary rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fs-6 mb-0 text-white" id="aiModalLabel">Executive AI Assistant</h5>
                            <small class="text-white-50" style="font-size: 0.75rem;">Asisten Cerdas Pengambilan Keputusan Dekan</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="ai-chat-box p-3 rounded-3 border" id="aiChatMessages" style="background: var(--surface-hover); min-height: 200px;">
                        <div class="ai-message bot">
                            👋 <strong>Selamat datang di DEIS AI Assistant.</strong><br>
                            Saya dapat menganalisis data fakultas, mengidentifikasi risiko BKD dosen, status akreditasi, hingga capaian IKU. Silakan ajukan pertanyaan atau klik saran di bawah:
                        </div>
                    </div>
                    <!-- Quick Prompt Chips -->
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Ringkas kondisi fakultas'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            📊 Ringkas Kondisi Fakultas
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Siapa dosen yang bermasalah BKD?'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            👨‍🏫 Dosen Bermasalah BKD
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill" onclick="document.getElementById('aiChatInput').value='Bagaimana status akreditasi prodi?'; document.getElementById('aiChatForm').dispatchEvent(new Event('submit'))">
                            🏛️ Radar Akreditasi
                        </button>
                    </div>
                </div>
                <div class="modal-footer p-3">
                    <form id="aiChatForm" class="d-flex w-100 gap-2">
                        <input type="text" id="aiChatInput" class="form-control" placeholder="Tanya AI seputar data fakultas..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                            <i class="fa-solid fa-paper-plane"></i> <span class="d-none d-sm-inline">Kirim</span>
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
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    <input type="text" id="globalSearchInput" class="form-control border-0 shadow-none ps-0" placeholder="Ketik untuk mencari Dosen, Mahasiswa, Rapat, RTL, IKU..." autocomplete="off">
                    <span class="badge bg-light text-muted border">ESC to close</span>
                </div>
                <div class="p-3" id="globalSearchResults" style="max-height: 380px; overflow-y: auto; background: var(--surface-hover);">
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-magnifying-glass fs-2 mb-2 d-block opacity-50"></i>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $baseUrl ?>/assets/js/main.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/app-deis.js"></script>

    <!-- Theme & Palette Engine (Sama Persis dengan OBE) -->
    <script>
        window.DEIS_BASE_URL = '<?= $baseUrl ?>';

        function applyTheme(theme, animate = false) {
            if (animate) {
                document.documentElement.classList.add('theme-transition');
            }
            
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(theme);
            localStorage.setItem('theme', theme);

            // Update UI Topbar Button
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            const btn = document.getElementById('themeToggleBtn');

            if (icon && text && btn) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                    btn.setAttribute('title', 'Ganti ke Mode Gelap (Dark Mode)');
                    btn.setAttribute('aria-label', 'Switch to Dark Mode');
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                    btn.setAttribute('title', 'Ganti ke Mode Terang (Light Mode)');
                    btn.setAttribute('aria-label', 'Switch to Light Mode');
                }
            }

            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));

            if (animate) {
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 350);
            }
        }

        window.toggleTheme = function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme, true);
        };

        const paletteConfig = {
            slate: { name: 'Slate Blue', color: '#0284c7' },
            indigo: { name: 'Indigo', color: '#6366f1' },
            emerald: { name: 'Emerald', color: '#10b981' },
            ocean: { name: 'Ocean Blue', color: '#0ea5e9' },
            violet: { name: 'Violet', color: '#8b5cf6' },
            amber: { name: 'Amber', color: '#f59e0b' },
            rose: { name: 'Rose', color: '#f43f5e' }
        };

        window.applyPalette = function(palette, animate = true) {
            if (!paletteConfig[palette]) {
                palette = 'slate';
            }

            if (animate) {
                document.documentElement.classList.add('theme-transition');
            }

            document.documentElement.setAttribute('data-palette', palette);
            localStorage.setItem('accent_palette', palette);

            // Update Topbar UI
            const nameEl = document.getElementById('paletteActiveName');
            const dotEl = document.getElementById('paletteActiveDot');
            if (nameEl) nameEl.textContent = paletteConfig[palette].name;
            if (dotEl) dotEl.style.backgroundColor = paletteConfig[palette].color;

            // Update active option in dropdown list
            document.querySelectorAll('.palette-option').forEach(btn => {
                if (btn.getAttribute('data-pal') === palette) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            const menu = document.getElementById('paletteDropdownMenu');
            if (menu) menu.style.display = 'none';

            window.dispatchEvent(new CustomEvent('paletteChanged', { 
                detail: { palette, color: paletteConfig[palette].color } 
            }));

            if (animate) {
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 350);
            }
        };

        window.togglePaletteDropdown = function(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('paletteDropdownMenu');
            if (menu) {
                const isHidden = menu.style.display === 'none' || menu.style.display === '';
                menu.style.display = isHidden ? 'block' : 'none';
            }
        };

        // Sidebar Collapse Logic
        function updateSidebarCollapseUI(collapsed) {
            const icon = document.getElementById('sidebarCollapseIcon');
            const label = document.querySelector('.collapse-label');
            const topbarBtn = document.getElementById('sidebarToggleBtn');

            if (icon) {
                icon.className = collapsed ? 'fa-solid fa-angles-right collapse-icon' : 'fa-solid fa-angles-left collapse-icon';
            }
            if (label) {
                label.textContent = collapsed ? 'Lebarkan' : 'Ciutkan Sidebar';
            }
            if (topbarBtn) {
                topbarBtn.setAttribute('title', collapsed ? 'Buka Lebar Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)');
            }
        }

        window.toggleSidebarCollapse = function() {
            const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
            const newState = !isCollapsed;

            if (newState) {
                document.documentElement.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', 'true');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', 'false');
            }

            updateSidebarCollapseUI(newState);
            window.dispatchEvent(new Event('resize'));
        };

        // Initialize Sidebar Collapse State on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            const initCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
            updateSidebarCollapseUI(initCollapsed);
        });

        // Sidebar Group Toggle Accordion
        window.toggleSidebarGroup = function(headerBtn) {
            const group = headerBtn.closest('.sidebar-group');
            if (group) {
                group.classList.toggle('expanded');
            }
        };

        // User Pill Profile Dropdown
        window.toggleUserDropdown = function(e) {
            if (e) e.stopPropagation();
            const notifMenu = document.getElementById('notifDropdownMenu');
            if (notifMenu) notifMenu.style.display = 'none';
            const palMenu = document.getElementById('paletteDropdownMenu');
            if (palMenu) palMenu.style.display = 'none';
            const branchMenu = document.getElementById('branchDropdownMenu');
            if (branchMenu) branchMenu.style.display = 'none';

            const menu = document.getElementById('userPillMenu');
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
            }
        };

        // Notification Bell Dropdown
        window.toggleNotifDropdown = function(e) {
            if (e) e.stopPropagation();
            const userMenu = document.getElementById('userPillMenu');
            if (userMenu) userMenu.style.display = 'none';
            const palMenu = document.getElementById('paletteDropdownMenu');
            if (palMenu) palMenu.style.display = 'none';
            const branchMenu = document.getElementById('branchDropdownMenu');
            if (branchMenu) branchMenu.style.display = 'none';

            const notifMenu = document.getElementById('notifDropdownMenu');
            if (notifMenu) {
                notifMenu.style.display = (notifMenu.style.display === 'none' || notifMenu.style.display === '') ? 'block' : 'none';
            }
        };

        // Mark All Notifications Read
        window.markAllNotifRead = function(e) {
            if (e) e.stopPropagation();
            const badge = document.getElementById('notifBadgeCount');
            const pulse = document.getElementById('notifPulseDot');
            const pill = document.getElementById('notifPillCount');
            const markBtn = document.getElementById('notifMarkReadBtn');
            const divider = document.getElementById('notifHeaderDivider');
            const unreadDots = document.querySelectorAll('.notif-unread-dot');
            const unreadItems = document.querySelectorAll('.notif-item.unread');

            if (badge) badge.style.display = 'none';
            if (pulse) pulse.style.display = 'none';
            if (pill) pill.style.display = 'none';
            if (markBtn) markBtn.style.display = 'none';
            if (divider) divider.style.display = 'none';
            unreadDots.forEach(dot => dot.style.display = 'none');
            unreadItems.forEach(item => item.classList.remove('unread'));

            const url = (window.DEIS_BASE_URL || '') + '/notifications/mark-read';
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            }).catch(err => console.warn('markAllNotifRead error:', err));
        };

        // Mark Single Notification Read on click
        window.markItemRead = function(id, e) {
            if (!id) return;
            const targetItem = e.currentTarget || (e.target ? e.target.closest('.notif-item') : null);
            if (targetItem && targetItem.classList.contains('unread')) {
                targetItem.classList.remove('unread');
                const dot = targetItem.querySelector('.notif-unread-dot');
                if (dot) dot.style.display = 'none';

                const badge = document.getElementById('notifBadgeCount');
                if (badge) {
                    let cur = parseInt(badge.textContent) || 0;
                    if (cur > 1) {
                        badge.textContent = (cur - 1) > 9 ? '9+' : (cur - 1);
                    } else {
                        badge.style.display = 'none';
                        const pulse = document.getElementById('notifPulseDot');
                        if (pulse) pulse.style.display = 'none';
                        const pill = document.getElementById('notifPillCount');
                        if (pill) pill.style.display = 'none';
                        const markBtn = document.getElementById('notifMarkReadBtn');
                        if (markBtn) markBtn.style.display = 'none';
                        const divider = document.getElementById('notifHeaderDivider');
                        if (divider) divider.style.display = 'none';
                    }
                }

                const url = (window.DEIS_BASE_URL || '') + '/notifications/read-item';
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                }).catch(err => console.warn('markItemRead error:', err));
            }
        };

        // Academic Year / Branch Pill Dropdown
        window.toggleBranchDropdown = function(e) {
            if (e) e.stopPropagation();
            const notifMenu = document.getElementById('notifDropdownMenu');
            if (notifMenu) notifMenu.style.display = 'none';
            const userMenu = document.getElementById('userPillMenu');
            if (userMenu) userMenu.style.display = 'none';
            const palMenu = document.getElementById('paletteDropdownMenu');
            if (palMenu) palMenu.style.display = 'none';

            const menu = document.getElementById('branchDropdownMenu');
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
            }
        };

        // Switch Academic Year / Semester Selection
        window.switchSemester = function(semesterNama) {
            const textEl = document.getElementById('activeSemesterText');
            if (textEl) textEl.textContent = semesterNama;
            localStorage.setItem('active_semester', semesterNama);

            const items = document.querySelectorAll('.branch-dropdown-item');
            items.forEach(btn => {
                const nameEl = btn.querySelector('.branch-item-name');
                const isTarget = nameEl && nameEl.textContent.trim() === semesterNama;
                if (isTarget) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            const menu = document.getElementById('branchDropdownMenu');
            if (menu) menu.style.display = 'none';
        };

        // Dropdown Close Listeners
        document.addEventListener('click', function(e) {
            const palWrapper = document.getElementById('paletteDropdownWrapper');
            const palMenu = document.getElementById('paletteDropdownMenu');
            if (palWrapper && palMenu && !palWrapper.contains(e.target)) {
                palMenu.style.display = 'none';
            }

            const branchWrapper = document.getElementById('branchPillWrapper');
            const branchMenu = document.getElementById('branchDropdownMenu');
            if (branchWrapper && branchMenu && !branchWrapper.contains(e.target)) {
                branchMenu.style.display = 'none';
            }

            const userWrapper = document.getElementById('userPillWrapper');
            const userMenu = document.getElementById('userPillMenu');
            if (userWrapper && userMenu && !userWrapper.contains(e.target)) {
                userMenu.style.display = 'none';
            }

            const notifWrapper = document.getElementById('notifDropdownWrapper');
            const notifMenu = document.getElementById('notifDropdownMenu');
            if (notifWrapper && notifMenu && !notifWrapper.contains(e.target)) {
                notifMenu.style.display = 'none';
            }
        });

        // Quick Search Menu (Sidebar Search Bar Filter)
        const sidebarSearch = document.getElementById('sidebarSearchInput');
        if (sidebarSearch) {
            sidebarSearch.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const links = document.querySelectorAll('.sidebar-nav-item');
                const groups = document.querySelectorAll('.sidebar-group');

                links.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (query.length > 0) {
                    groups.forEach(g => g.classList.add('expanded'));
                }
            });

            // Keyboard shortcut '/' to focus sidebar search
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    sidebarSearch.focus();
                }
                // Ctrl+B to toggle sidebar
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    window.toggleSidebarCollapse();
                }
            });
        }

        // Initialize theme on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme') || 'dark';
            applyTheme(currentTheme, false);

            const currentPalette = document.documentElement.getAttribute('data-palette') || localStorage.getItem('accent_palette') || 'slate';
            window.applyPalette(currentPalette, false);
        });
    </script>
</body>
</html>
