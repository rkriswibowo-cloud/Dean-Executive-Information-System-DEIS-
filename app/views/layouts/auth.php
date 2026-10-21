<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Masuk Sistem', ENT_QUOTES, 'UTF-8') ?> - <?= $appConfig['short_name'] ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.45.0/tabler-icons.min.css">

    <!-- Bootstrap 5 CSS (for utility & grid compatibility) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- DEIS Theme System CSS -->
    <?php
    $themeCssPath = __DIR__ . '/../../../public/assets/css/app-theme.css';
    $themeCssVer = file_exists($themeCssPath) ? filemtime($themeCssPath) : time();
    ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/app-theme.css?v=<?= $themeCssVer ?>">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/auth-login.css?v=20261004-v1">

    <!-- Anti-FOUC Script -->
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
            } catch(e) {}
        })();
    </script>

    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            display: block;
            padding: 0;
            background-color: var(--background);
            position: relative;
            overflow: hidden;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--surface) !important;
            border: 1px solid var(--border) !important;
            border-radius: 20px !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45) !important;
            padding: 36px 32px;
            position: relative;
            z-index: 10;
        }

        .auth-brand-squircle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 16px;
            box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.35);
        }

        .glass-input-wrapper {
            background: var(--surface-hover);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            transition: all 0.2s ease;
        }

        .glass-input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
            background: var(--surface);
        }

        .glass-input-wrapper input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 12px 10px !important;
            width: 100%;
            color: var(--text-main) !important;
        }

        .glass-input-wrapper input:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        .auth-top-nav {
            position: fixed;
            top: 20px;
            right: 24px;
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <!-- Top Right Theme Mode Selector -->
        <div class="auth-top-nav">
            <button type="button" id="themeToggleBtn" class="theme-toggle-btn" onclick="toggleAuthTheme()" title="Ganti Mode Gelap / Terang">
                <span class="theme-toggle-icon">
                    <i class="fa-solid fa-moon" id="themeToggleIcon"></i>
                </span>
                <span id="themeToggleText" style="font-size: 0.82rem;">Dark</span>
            </button>
        </div>

        <!-- Render Content -->
        <?= $content ?>
    </div>

    <!-- Theme Toggle Script -->
    <script>
        function toggleAuthTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.documentElement.setAttribute('data-bs-theme', next);
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(next);
            localStorage.setItem('theme', next);

            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (icon && text) {
                if (next === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const theme = document.documentElement.getAttribute('data-theme') || 'dark';
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (icon && text) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                }
            }
        });
    </script>
</body>
</html>
