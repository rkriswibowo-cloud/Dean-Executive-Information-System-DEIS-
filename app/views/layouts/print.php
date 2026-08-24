<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Laporan Eksekutif', ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1.5cm; size: A4; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border">
        <a href="<?= $baseUrl ?>/reports" class="btn btn-secondary btn-sm px-3 fw-semibold">
            ← Kembali ke Laporan Eksekutif
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-semibold">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat text-center">
        <h5 class="mb-0 fw-bold">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h5>
        <h4 class="mb-0 fw-bold">UNIVERSITAS DEIS INDONESIA</h4>
        <h5 class="mb-1 fw-bold"><?= strtoupper(htmlspecialchars($faculty['name'] ?? 'FAKULTAS TEKNOLOGI INFORMASI DAN KOMPUTER', ENT_QUOTES, 'UTF-8')) ?></h5>
        <p class="mb-0 small">Gedung Dekanat Kampus Terpadu, Jl. Pendidikan No. 1 • Telp: (021) 7891234 • Email: dekanat@<?= strtolower(htmlspecialchars($faculty['code'] ?? 'deis')) ?>.ac.id</p>
    </div>

    <?= $content ?>

    <!-- Tanda Tangan Dekan -->
    <div class="row mt-5 pt-4">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <p class="mb-1">Ditetapkan di: Jakarta</p>
            <p class="mb-0">Pada tanggal: <?= date('d F Y') ?></p>
            <p class="fw-bold mb-5">Dekan <?= htmlspecialchars($faculty['code'] ?? 'FTIK', ENT_QUOTES, 'UTF-8') ?>,</p>
            <br><br>
            <p class="fw-bold mb-0 text-decoration-underline"><?= htmlspecialchars($deanName ?? ($faculty['dean_name'] ?? 'Prof. Dr. Ir. Hendra Wijaya, M.Kom.'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="small text-muted mb-0">NIP. <?= htmlspecialchars($deanNip ?? '197805122003121002', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</body>
</html>
