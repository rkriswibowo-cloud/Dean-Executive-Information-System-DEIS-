<?php 
use App\Helpers\FormatHelper; 

// Hitung Skor Komposit KPI Tri Dharma Dosen
$kpiScore = ($lecturer['sinta_score'] * 0.4) + 
            ($lecturer['publication_count'] * 15) + 
            ($lecturer['pkm_count'] * 10) + 
            ($lecturer['hki_count'] * 20) + 
            ($lecturer['attendance_percentage'] * 0.5);

// Inisial Dosen
$nameClean = preg_replace('/^(Dr\.|Prof\.|Drs\.|Ir\.|H\.|Hj\.)\s+/i', '', trim($lecturer['name']));
$nameParts = explode(' ', $nameClean);
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} else {
    $initials = strtoupper(substr($nameClean, 0, 2));
}

// BKD & Teaching Load Calculations
$sksLoad = (float)($lecturer['teaching_load_sks'] ?? 0);
$sksPercentage = min(100, round(($sksLoad / 16) * 100));
$attendance = (float)($lecturer['attendance_percentage'] ?? 0);
$isCertified = (stripos($lecturer['certification_status'] ?? '', 'Tersertifikasi') !== false || stripos($lecturer['certification_status'] ?? '', 'Sudah') !== false);
?>

<!-- Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= $baseUrl ?>/lecturers" class="btn btn-sm btn-soft-secondary d-inline-flex align-items-center gap-1.5 px-3 py-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Dosen
        </a>
        <div class="vr opacity-25 d-none d-sm-block" style="height: 24px;"></div>
        <div>
            <h4 class="fw-bold mb-0 text-body">Profil Kinerja & Portofolio Dosen</h4>
            <small class="text-muted">Data Induk Dosen, Capaian Tri Dharma, dan Indikator Kinerja Utama (IKU)</small>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold">
            <i class="fa-solid fa-circle-check me-1"></i> Status: <?= htmlspecialchars($lecturer['status'] ?? 'Aktif', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-soft-primary d-inline-flex align-items-center gap-1.5 px-3 py-2">
            <i class="fa-solid fa-print"></i> Cetak Portofolio
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT COLUMN: Profile Hero & Identity Card -->
    <div class="col-12 col-lg-4">
        <!-- Main Profile Card -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <!-- Cover Banner Backdrop -->
            <div class="profile-cover position-relative" style="height: 100px; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.3) 0%, rgba(var(--primary-rgb), 0.08) 100%); border-bottom: 1px solid var(--border);">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fa-solid fa-graduation-cap fs-1 text-primary"></i>
                </div>
            </div>

            <!-- Profile Body with Overlapping Avatar -->
            <div class="card-body p-4 pt-0 text-center position-relative">
                <!-- Avatar with subtle ring -->
                <div class="d-inline-flex align-items-center justify-content-center fw-bold shadow-md position-relative"
                     style="width: 90px; height: 90px; margin-top: -45px; border-radius: 50%; border: 4px solid var(--surface); background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); color: #ffffff; font-size: 2rem;">
                    <?= $initials ?>
                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-2 border-white rounded-circle" style="width: 16px; height: 16px;" title="Status Aktif"></span>
                </div>

                <h5 class="fw-bold mt-3 mb-1 text-body"><?= htmlspecialchars($lecturer['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                <p class="text-muted small mb-2 font-monospace">NIDN: <?= htmlspecialchars($lecturer['nidn'], ENT_QUOTES, 'UTF-8') ?></p>

                <!-- Tags / Badges -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-1.5 mb-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                        <i class="fa-solid fa-award me-1"></i><?= $lecturer['academic_rank'] ?>
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1">
                        <i class="fa-solid fa-user-graduate me-1"></i><?= $lecturer['education_level'] ?>
                    </span>
                    <span class="badge bg-light-subtle text-body border px-2.5 py-1">
                        <i class="fa-solid fa-landmark me-1"></i><?= htmlspecialchars($program['name'] ?? 'Prodi', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <!-- Contact Details -->
                <div class="p-3 rounded-3 mb-3 text-start" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                    <div class="d-flex align-items-center gap-2 mb-2 text-truncate">
                        <span class="text-primary" style="width: 20px;"><i class="fa-regular fa-envelope"></i></span>
                        <a href="mailto:<?= htmlspecialchars($lecturer['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-body small text-truncate">
                            <?= htmlspecialchars($lecturer['email'] ?? 'Belum ada email', ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-success" style="width: 20px;"><i class="fa-solid fa-phone"></i></span>
                        <span class="text-body small"><?= htmlspecialchars($lecturer['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <!-- Academic Specs Table -->
                <div class="text-start small">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-certificate me-2 text-primary"></i>Sertifikasi Dosen</span>
                        <?php if ($isCertified): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="fa-solid fa-check me-1"></i>Tersertifikasi
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                Belum Tersertifikasi
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-clipboard-check me-2 text-info"></i>Status BKD</span>
                        <?= FormatHelper::statusBadge($lecturer['bkd_status']) ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-chalkboard-user me-2 text-secondary"></i>Beban Mengajar</span>
                        <span class="fw-bold text-body"><?= $lecturer['teaching_load_sks'] ?> SKS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted"><i class="fa-solid fa-user-check me-2 text-success"></i>Kehadiran Kuliah</span>
                        <span class="fw-bold text-success"><?= $lecturer['attendance_percentage'] ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teaching Load & Attendance Gauge Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="card-title fw-bold mb-0 text-body">
                    <i class="fa-solid fa-chart-pie text-primary me-2"></i>Beban Kerja & Kepatuhan
                </h6>
            </div>
            <div class="card-body p-4">
                <!-- Kehadiran Mengajar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-muted">Presensi Kuliah Semester Ini</span>
                        <span class="small fw-bold <?= $attendance >= 90 ? 'text-success' : 'text-warning' ?>"><?= $attendance ?>%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--surface-hover);">
                        <div class="progress-bar <?= $attendance >= 90 ? 'bg-success' : 'bg-warning' ?>" role="progressbar" style="width: <?= $attendance ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.75rem;">
                        <span>Standar Minimal: 80%</span>
                        <span class="text-success"><i class="fa-solid fa-circle-check"></i> Memenuhi</span>
                    </div>
                </div>

                <!-- SKS Beban Mengajar -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-muted">Beban Mengajar (SKS)</span>
                        <span class="small fw-bold text-primary"><?= $sksLoad ?> / 16 SKS</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--surface-hover);">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $sksPercentage ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.75rem;">
                        <span>Beban Ideal: 12–16 SKS</span>
                        <span class="text-primary"><i class="fa-solid fa-circle-check"></i> Optimal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Tri Dharma Performance & Research Impact -->
    <div class="col-12 col-lg-8">
        <!-- 4 Top Executive KPI Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card kpi-card-vertical h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="kpi-title mb-0" style="font-size: 0.75rem; letter-spacing: 0.02em;">Skor SINTA</span>
                        <span class="kpi-icon-wrap" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="fa-solid fa-award"></i>
                        </span>
                    </div>
                    <div>
                        <div class="kpi-value text-warning" style="font-size: 1.7rem; font-weight: 800; line-height: 1.1;">
                            <?= number_format((float)$lecturer['sinta_score'], 0) ?>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Skor Sitasi Dikti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card kpi-card-vertical h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="kpi-title mb-0" style="font-size: 0.75rem; letter-spacing: 0.02em;">Scopus H-Index</span>
                        <span class="kpi-icon-wrap" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                    </div>
                    <div>
                        <div class="kpi-value text-success" style="font-size: 1.7rem; font-weight: 800; line-height: 1.1;">
                            <?= $lecturer['scopus_h_index'] ?>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Sitasi Global Scopus</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card kpi-card-vertical h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="kpi-title mb-0" style="font-size: 0.75rem; letter-spacing: 0.02em;">Publikasi</span>
                        <span class="kpi-icon-wrap" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 8px; background: rgba(var(--primary-rgb), 0.15); color: var(--primary);">
                            <i class="fa-solid fa-book-open"></i>
                        </span>
                    </div>
                    <div>
                        <div class="kpi-value text-primary" style="font-size: 1.7rem; font-weight: 800; line-height: 1.1;">
                            <?= $lecturer['publication_count'] ?> <span class="fs-6 fw-normal text-muted">Karya</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Jurnal & Prosiding</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card kpi-card-vertical h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="kpi-title mb-0" style="font-size: 0.75rem; letter-spacing: 0.02em;">Skor KPI</span>
                        <span class="kpi-icon-wrap" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 8px; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                            <i class="fa-solid fa-star"></i>
                        </span>
                    </div>
                    <div>
                        <div class="kpi-value text-info" style="font-size: 1.7rem; font-weight: 800; line-height: 1.1;">
                            <?= number_format($kpiScore, 0) ?> <span class="fs-6 fw-normal text-muted">Poin</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Komposit Tri Dharma</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Output & Tri Dharma Summary Cards -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="card-title fw-bold mb-0 text-body">Rekapitulasi Luaran Akademik & Tri Dharma</h5>
                    <small class="text-muted">Distribusi luaran tridharma perguruan tinggi semester aktif</small>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 font-monospace">
                    Tahun Akademik 2025/2026
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- PkM -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 h-100 position-relative" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Pengabdian</span>
                                <span class="fs-4 text-primary"><i class="fa-solid fa-hands-holding-circle"></i></span>
                            </div>
                            <h4 class="fw-bold mb-1 text-body"><?= $lecturer['pkm_count'] ?> <span class="fs-6 fw-normal text-muted">Kegiatan</span></h4>
                            <p class="text-muted small mb-0">Pengabdian kepada Masyarakat (PkM) didanai institusi/hibah</p>
                        </div>
                    </div>

                    <!-- HKI -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 h-100 position-relative" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Kekayaan Intelektual</span>
                                <span class="fs-4 text-success"><i class="fa-solid fa-certificate"></i></span>
                            </div>
                            <h4 class="fw-bold mb-1 text-body"><?= $lecturer['hki_count'] ?> <span class="fs-6 fw-normal text-muted">HKI / Paten</span></h4>
                            <p class="text-muted small mb-0">Hak Cipta, Paten, atau Desain Industri terdaftar resmi</p>
                        </div>
                    </div>

                    <!-- Buku Ber-ISBN -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 h-100 position-relative" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Buku Ajar</span>
                                <span class="fs-4 text-warning"><i class="fa-solid fa-book"></i></span>
                            </div>
                            <h4 class="fw-bold mb-1 text-body"><?= $lecturer['books_count'] ?> <span class="fs-6 fw-normal text-muted">Buku ISBN</span></h4>
                            <p class="text-muted small mb-0">Buku Referensi, Monograf, atau Buku Teks Pengajaran</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Breakdown Tabs: Tri Dharma, Riset & Pembelajaran -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="lecturerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-3 py-1.5" id="tridharma-tab" data-bs-toggle="tab" data-bs-target="#tab-tridharma" type="button" role="tab">
                            <i class="fa-solid fa-layer-group me-1.5"></i>Kinerja 4 Pilar Tri Dharma
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold px-3 py-1.5" id="bkd-tab" data-bs-toggle="tab" data-bs-target="#tab-bkd" type="button" role="tab">
                            <i class="fa-solid fa-clipboard-check me-1.5"></i>Evaluasi BKD & IKU
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="lecturerTabsContent">
                    <!-- Tab 1: Kinerja 4 Pilar -->
                    <div class="tab-pane fade show active" id="tab-tridharma" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-body-tertiary text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Pilar Tri Dharma</th>
                                        <th>Indikator Utama</th>
                                        <th>Realisasi Kinerja</th>
                                        <th>Standar / Target</th>
                                        <th class="text-end pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-3 fw-bold text-body">
                                            <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Pendidikan & Pengajaran
                                        </td>
                                        <td>Beban SKS & Presensi Tatap Muka</td>
                                        <td>
                                            <strong><?= $lecturer['teaching_load_sks'] ?> SKS</strong> 
                                            <span class="text-muted">(<?= $lecturer['attendance_percentage'] ?>% Hadir)</span>
                                        </td>
                                        <td>12–16 SKS / Sem</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-check me-1"></i>Tercapai
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 fw-bold text-body">
                                            <i class="fa-solid fa-flask-vial text-info me-2"></i>Penelitian & Publikasi
                                        </td>
                                        <td>Karya Ilmiah & H-Index</td>
                                        <td>
                                            <strong><?= $lecturer['publication_count'] ?> Publikasi</strong> 
                                            <span class="text-muted">(H-Index: <?= $lecturer['scopus_h_index'] ?>)</span>
                                        </td>
                                        <td>Min. 1 Publikasi/Tahun</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-check me-1"></i>Sangat Baik
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 fw-bold text-body">
                                            <i class="fa-solid fa-hands-holding-circle text-warning me-2"></i>Pengabdian Masyarakat (PkM)
                                        </td>
                                        <td>Program Kemitraan & Bina Lingkungan</td>
                                        <td>
                                            <strong><?= $lecturer['pkm_count'] ?> Program</strong>
                                        </td>
                                        <td>Min. 1 PkM/Tahun</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-check me-1"></i>Tercapai
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 fw-bold text-body">
                                            <i class="fa-solid fa-certificate text-success me-2"></i>Luaran Intelektual & Buku
                                        </td>
                                        <td>Hak Cipta (HKI) & Buku Ber-ISBN</td>
                                        <td>
                                            <strong><?= $lecturer['hki_count'] ?> HKI</strong>, <strong><?= $lecturer['books_count'] ?> Buku</strong>
                                        </td>
                                        <td>Target IKU Fakultas</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-check me-1"></i>Produktif
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Evaluasi BKD & IKU -->
                    <div class="tab-pane fade" id="tab-bkd" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                                    <h6 class="fw-bold mb-2 text-body"><i class="fa-solid fa-shield-halved text-success me-2"></i>Kepatuhan BKD Semester</h6>
                                    <p class="small text-muted mb-2">
                                        Hasil verifikasi asesor Beban Kerja Dosen (BKD) menunjukkan bahwa dosen yang bersangkutan telah memenuhi rubrik beban kinerja minimum (12 SKS) dan maksimum (16 SKS).
                                    </p>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-semibold">
                                            Status: <?= $lecturer['bkd_status'] ?>
                                        </span>
                                        <span class="badge bg-light-subtle text-body border px-2.5 py-1.5">
                                            Semester Genap 2025/2026
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background-color: var(--surface-hover); border: 1px solid var(--border);">
                                    <h6 class="fw-bold mb-2 text-body"><i class="fa-solid fa-bullseye text-primary me-2"></i>Kontribusi IKU Fakultas</h6>
                                    <p class="small text-muted mb-2">
                                        Dosen menyumbang poin signifikan pada <strong>IKU 3 (Dosen Berkegiatan di Luar Kampus)</strong> dan <strong>IKU 5 (Hasil Kerja Dosen Digunakan oleh Masyarakat / Rekognisi Internasional)</strong>.
                                    </p>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-semibold">
                                            Kontributor Aktif IKU
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

