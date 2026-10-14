<?php use App\Helpers\FormatHelper; ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-body mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-contract text-primary"></i> Laporan Eksekutif Dekan Fakultas
        </h4>
        <p class="text-muted small mb-0">Konsolidasi laporan capaian indikator kinerja fakultas, komparasi program studi, SDM dosen, dan serapan anggaran.</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <a href="<?= $baseUrl ?>/reports/export?type=csv" class="btn btn-sm btn-soft-success rounded-3 px-3 shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-csv"></i> Ekspor CSV / Excel
        </a>
        <a href="<?= $baseUrl ?>/reports/export?type=print" target="_blank" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-print"></i> Pratinjau Cetak / PDF
        </a>
    </div>
</div>

<!-- Executive Summary Cards -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-bullseye text-primary"></i> 1. Ringkasan Capaian Indikator Kinerja Utama (IKU 2026)
        </h5>
        <span class="badge badge-soft-primary">Total <?= count($indicators) ?> Indikator</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Indikator</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Capaian (%)</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($indicators as $ind): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-body">
                                <span class="badge badge-soft-secondary font-monospace"><?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="fw-medium text-body"><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td><strong class="text-body"><?= $ind['target_value'] ?></strong> <span class="text-muted small"><?= $ind['unit'] ?></span></td>
                            <td><strong class="text-primary"><?= $ind['realization_value'] ?? 0 ?></strong> <span class="text-muted small"><?= $ind['unit'] ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="text-body"><?= $ind['achievement_percentage'] ?? 0 ?>%</strong>
                                    <div class="progress flex-grow-1" style="height: 5px; width: 60px;">
                                        <div class="progress-bar bg-<?= ($ind['achievement_percentage'] ?? 0) >= 100 ? 'success' : (($ind['achievement_percentage'] ?? 0) >= 70 ? 'info' : 'warning') ?>" style="width: <?= min(100, $ind['achievement_percentage'] ?? 0) ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($ind['realization_status'] ?? 'Proses') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Comparison of Study Programs -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-graduation-cap text-primary"></i> 2. Matriks Komparasi Kinerja Program Studi
        </h5>
        <span class="badge badge-soft-info"><?= count($programs) ?> Program Studi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Program Studi</th>
                        <th>Jenjang</th>
                        <th>Peringkat Akreditasi</th>
                        <th>Jml Mahasiswa</th>
                        <th>Jml Dosen</th>
                        <th class="text-end pe-4">Target Retensi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $p): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-body"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge badge-soft-secondary"><?= htmlspecialchars($p['degree'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= FormatHelper::statusBadge($p['accreditation_status']) ?> <span class="text-muted small ms-1">(Skor: <?= $p['accreditation_score'] ?>)</span></td>
                            <td><strong class="text-body"><?= number_format($p['student_count']) ?></strong> <span class="text-muted small">Mhs</span></td>
                            <td><strong class="text-body"><?= number_format($p['lecturer_count']) ?></strong> <span class="text-muted small">Dosen</span></td>
                            <td class="text-end pe-4"><strong class="text-success"><?= $p['target_retention'] ?>%</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

