<?php use App\Helpers\FormatHelper; ?>
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-4 text-center">
            <div class="card-body">
                <div class="avatar avatar-xxl bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-1 shadow">
                    <?= strtoupper(substr($lecturer['name'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($lecturer['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-muted small mb-2">NIDN: <?= htmlspecialchars($lecturer['nidn'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="badge bg-primary-subtle text-primary px-3 py-1 mb-3"><?= $lecturer['academic_rank'] ?> • <?= $program['name'] ?></span>

                <hr class="my-3">

                <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Jenjang Pendidikan</span>
                        <strong><?= $lecturer['education_level'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Sertifikasi</span>
                        <strong><?= $lecturer['certification_status'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Status BKD</span>
                        <?= FormatHelper::statusBadge($lecturer['bkd_status']) ?>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Beban Mengajar</span>
                        <strong><?= $lecturer['teaching_load_sks'] ?> SKS</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Kehadiran Perkuliahan</span>
                        <strong><?= $lecturer['attendance_percentage'] ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card card-lg shadow-sm border-0 rounded-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">Capaian Tri Dharma & Riset Dosen</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small">Skor SINTA</span>
                            <h3 class="fw-bold mb-0 text-primary mt-1"><?= $lecturer['sinta_score'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small">Scopus H-Index</span>
                            <h3 class="fw-bold mb-0 text-success mt-1"><?= $lecturer['scopus_h_index'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small">Total Publikasi</span>
                            <h3 class="fw-bold mb-0 text-info mt-1"><?= $lecturer['publication_count'] ?> Karya</h3>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Rekapitulasi Luaran Akademik</h6>
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ti ti-book me-2 text-primary"></i>Pengabdian kepada Masyarakat (PkM)</span>
                        <span class="badge bg-primary rounded-pill"><?= $lecturer['pkm_count'] ?> Kegiatan</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ti ti-certificate me-2 text-success"></i>Hak Kekayaan Intelektual (HKI / Paten)</span>
                        <span class="badge bg-success rounded-pill"><?= $lecturer['hki_count'] ?> HKI</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ti ti-bookmark me-2 text-warning"></i>Buku Referensi / Ajar Ber-ISBN</span>
                        <span class="badge bg-warning text-dark rounded-pill"><?= $lecturer['books_count'] ?> Buku</span>
                    </li>
                </ul>

                <a href="<?= $baseUrl ?>/lecturers" class="btn btn-outline-secondary">
                    ← Kembali ke Daftar Dosen
                </a>
            </div>
        </div>
    </div>
</div>
