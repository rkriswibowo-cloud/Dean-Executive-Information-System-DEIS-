<div class="row justify-content-center py-5">
    <div class="col-12 col-md-8 col-lg-6 text-center">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="card-body">
                <div class="mb-4 text-warning">
                    <i class="fa-solid fa-triangle-exclamation display-1"></i>
                </div>
                <h2 class="fw-bold text-body mb-2">404 &bull; Halaman Tidak Ditemukan</h2>
                <p class="text-muted mb-4">Halaman <code class="px-2 py-1 bg-body-tertiary rounded"><?= htmlspecialchars($requestedPath ?? '', ENT_QUOTES, 'UTF-8') ?></code> yang Anda cari tidak tersedia atau tautan telah dipindahkan.</p>
                <a href="<?= $baseUrl ?>/dashboard" class="btn btn-primary rounded-3 px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-house"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

