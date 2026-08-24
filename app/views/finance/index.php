<?php use App\Helpers\FormatHelper; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Pagu Anggaran RKA (2026)</span>
            <h3 class="fw-bold mb-0 mt-1"><?= FormatHelper::currency($totalBudget) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Realisasi Belanja</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= FormatHelper::currency($totalRealized) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Tingkat Serapan Anggaran</span>
            <h3 class="fw-bold mb-0 mt-1 text-success"><?= $overallAbsorption ?>%</h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Rincian Anggaran RKA & Realisasi Keuangan Fakultas</h5>
        <span class="badge bg-primary-subtle text-primary">Tahun Anggaran 2026</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kategori & Pos Anggaran</th>
                        <th>Program Studi / Unit</th>
                        <th>Pagu Anggaran</th>
                        <th>Realisasi Belanja</th>
                        <th>Persentase Serapan</th>
                        <th class="text-end pe-4">Status Efisiensi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finances as $f): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= $f['category'] ?></span>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><?= htmlspecialchars($f['program_name'] ?? 'Dekanat Fakultas', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= FormatHelper::currency($f['budgeted_amount']) ?></strong></td>
                            <td><strong class="text-primary"><?= FormatHelper::currency($f['realized_amount']) ?></strong></td>
                            <td>
                                <strong><?= $f['absorption_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-<?= $f['absorption_percentage'] >= 85 ? 'success' : ($f['absorption_percentage'] >= 70 ? 'warning' : 'danger') ?>" style="width: <?= $f['absorption_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($f['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
