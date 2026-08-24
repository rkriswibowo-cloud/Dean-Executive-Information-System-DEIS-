<div class="card card-lg shadow-sm border-0 text-center p-4">
    <div class="card-body">
        <div class="mb-3 text-warning">
            <i class="ti ti-alert-triangle" style="font-size: 4rem;"></i>
        </div>
        <h1 class="h3 fw-bold mb-2">404 - Halaman Tidak Ditemukan</h1>
        <p class="text-muted mb-4">Halaman <code><?= htmlspecialchars($requestedPath ?? '', ENT_QUOTES, 'UTF-8') ?></code> yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        <a href="<?= $baseUrl ?>/dashboard" class="btn btn-primary">
            <i class="ti ti-home me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
