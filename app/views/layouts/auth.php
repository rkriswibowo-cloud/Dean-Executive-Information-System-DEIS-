<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Masuk Sistem', ENT_QUOTES, 'UTF-8') ?> - <?= $appConfig['short_name'] ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg">

    <!-- Color modes script -->
    <script src="<?= $baseUrl ?>/assets/js/vendors/color-modes.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.45.0/tabler-icons.min.css">

    <!-- Dasher Theme CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/theme.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/custom-deis.css">
</head>
<body class="auth-page">
    <!-- Top Right Theme Mode Selector -->
    <div class="position-absolute top-0 end-0 p-3 p-md-4 z-3">
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-2 shadow-sm bg-body px-3 py-1.5" type="button" data-bs-toggle="dropdown" aria-label="Toggle theme">
                <i class="ti ti-moon-stars theme-icon-active"></i>
                <span class="small fw-semibold d-none d-sm-inline">Tema</span>
                <i class="ti ti-chevron-down opacity-50" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="light">
                        <i class="ti ti-sun"></i> Mode Terang
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="dark">
                        <i class="ti ti-moon-stars"></i> Mode Gelap
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="auto">
                        <i class="ti ti-device-laptop"></i> Otomatis
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container py-4 py-md-5 position-relative z-1">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <?= $content ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
