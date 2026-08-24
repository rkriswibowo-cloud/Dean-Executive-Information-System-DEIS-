<?php use App\Helpers\FormatHelper; ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-file-analytics text-primary fs-2"></i> Laporan Eksekutif Dekan Fakultas
        </h3>
        <p class="text-muted small mb-0">Konsolidasi laporan capaian kinerja fakultas, perbandingan program studi, SDM dosen, dan serapan anggaran.</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <a href="<?= $baseUrl ?>/reports/export?type=csv" class="btn btn-outline-success shadow-sm btn-crud">
            <i class="ti ti-file-spreadsheet"></i> Ekspor CSV / Excel
        </a>
        <a href="<?= $baseUrl ?>/reports/export?type=print" class="btn btn-primary shadow-sm btn-crud">
            <i class="ti ti-printer"></i> Pratinjau Cetak / PDF
        </a>
    </div>
</div>

<!-- Executive Summary Cards -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3">
        <h5 class="card-title fw-bold mb-0">1. Ringkasan Capaian Indikator Kinerja Utama (IKU 2026)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                            <td class="ps-4 fw-bold text-dark"><?= $ind['code'] ?></td>
                            <td><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= $ind['target_value'] ?></strong> <?= $ind['unit'] ?></td>
                            <td><strong class="text-primary"><?= $ind['realization_value'] ?? 0 ?></strong> <?= $ind['unit'] ?></td>
                            <td><strong><?= $ind['achievement_percentage'] ?? 0 ?>%</strong></td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($ind['realization_status'] ?? 'Proses') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Comparison of Study Programs -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3">
        <h5 class="card-title fw-bold mb-0">2. Matriks Komparasi Kinerja Program Studi</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $p['degree'] ?></span></td>
                            <td><?= FormatHelper::statusBadge($p['accreditation_status']) ?> (Skor: <?= $p['accreditation_score'] ?>)</td>
                            <td><strong><?= number_format($p['student_count']) ?></strong> Mahasiswa</td>
                            <td><strong><?= number_format($p['lecturer_count']) ?></strong> Dosen</td>
                            <td class="text-end pe-4"><strong><?= $p['target_retention'] ?>%</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
