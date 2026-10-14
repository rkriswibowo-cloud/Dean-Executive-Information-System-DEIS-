<?php use App\Helpers\FormatHelper; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Standar Mutu</span>
                <span class="kpi-icon bg-primary-subtle text-primary">
                    <i class="ti ti-certificate"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-0"><?= $totalStandards ?> <span class="fs-6 fw-normal text-muted">Standar</span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Standar Tercapai</span>
                <span class="kpi-icon bg-success-subtle text-success">
                    <i class="ti ti-circle-check"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-0 text-success"><?= $achievedCount ?> <span class="fs-6 fw-normal text-muted">Standar</span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Dalam Proses</span>
                <span class="kpi-icon bg-info-subtle text-info">
                    <i class="ti ti-progress"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-0 text-info"><?= $processCount ?> <span class="fs-6 fw-normal text-muted">Standar</span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Belum Tercapai</span>
                <span class="kpi-icon bg-danger-subtle text-danger">
                    <i class="ti ti-alert-triangle"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-0 text-danger"><?= $unachievedCount ?> <span class="fs-6 fw-normal text-muted">Standar</span></h3>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0">Standar Mutu SPMI & Siklus PPEPP</h5>
            <small class="text-muted">Monitoring ketercapaian siklus PPEPP penjaminan mutu fakultas</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/quality/ami" class="btn btn-sm btn-soft-primary btn-crud-sm">
                <i class="ti ti-checklist"></i> Audit Mutu Internal (AMI)
            </a>
            <a href="<?= $baseUrl ?>/quality/surveys" class="btn btn-sm btn-soft-secondary btn-crud-sm">
                <i class="ti ti-chart-dots"></i> Survei Kepuasan
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode & Kategori</th>
                        <th>Pernyataan Standar</th>
                        <th>Target Metrik</th>
                        <th>Capaian Saat Ini</th>
                        <th>Tahapan PPEPP</th>
                        <th>PIC</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standards as $std): ?>
                        <tr class="<?= $std['status'] === 'Belum Tercapai' ? 'table-warning' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light-subtle text-body border"><?= $std['code'] ?></span>
                                <div class="small text-muted mt-1"><?= $std['category'] ?></div>
                            </td>
                            <td class="fw-semibold" style="max-width: 280px;">
                                <?= htmlspecialchars($std['name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><strong><?= $std['target_metric'] ?></strong></td>
                            <td><strong class="text-primary"><?= $std['current_metric'] ?></strong></td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                    <?= $std['ppepp_stage'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($std['pic'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($std['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

