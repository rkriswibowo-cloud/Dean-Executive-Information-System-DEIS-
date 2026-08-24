<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-radar text-danger fs-2"></i> Command Center Eksekutif Dekan
        </h3>
        <p class="text-muted small mb-0">Pusat kendali, deteksi dini krisis, radar deadline, dan eksekusi persetujuan fakultas.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= $baseUrl ?>/command-center/approvals" class="btn btn-warning shadow-sm btn-crud">
            <i class="ti ti-checklist"></i> Kelola Approvals (<?= $attention['pending_approvals'] ?>)
        </a>
    </div>
</div>

<!-- 1. Critical Alerts Panel (PRD Section 8.1) -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-danger d-flex align-items-center gap-2">
            <i class="ti ti-alert-octagon fs-4"></i> Critical & Warning Alerts Radar
        </h5>
        <span class="badge bg-danger rounded-pill px-3 py-1"><?= count($criticalAlerts) ?> Peringatan Aktif</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($criticalAlerts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="ti ti-circle-check text-success fs-1 mb-2 d-block"></i>
                Tidak ada peringatan kritis. Seluruh operasional fakultas dalam batas normal.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Tingkat</th>
                            <th>Kategori</th>
                            <th>Deskripsi Masalah</th>
                            <th>Aksi Tindakan Dekan</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criticalAlerts as $alert): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-<?= $alert['severity'] === 'Critical' ? 'danger' : ($alert['severity'] === 'Warning' ? 'warning' : 'info') ?>-subtle text-<?= $alert['severity'] === 'Critical' ? 'danger' : ($alert['severity'] === 'Warning' ? 'warning-emphasis' : 'info') ?> border px-2 py-1">
                                        <?= strtoupper($alert['severity']) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($alert['alert_type'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($alert['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($alert['description'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($alert['target_url'])): ?>
                                        <a href="<?= $baseUrl ?>/<?= $alert['target_url'] ?>" class="btn btn-sm btn-primary btn-crud-sm">
                                            Periksa <i class="ti ti-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="table-actions">
                                        <form action="<?= $baseUrl ?>/command-center/resolve-alert" method="POST" class="d-inline">
                                            <?= CsrfHelper::tokenField() ?>
                                            <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary btn-crud-sm" onclick="return confirm('Tandai peringatan ini sebagai telah diselesaikan?');">
                                                <i class="ti ti-check"></i> Selesai
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 2. Deadline Radar (PRD Section 8.2) -->
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-clock-exclamation text-warning fs-4"></i> Deadline Radar (Waktu Tersisa)
                </h5>
                <span class="badge bg-warning text-dark"><?= count($deadlines) ?> Deadline</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item & Kategori</th>
                                <th>Jatuh Tempo</th>
                                <th>Sisa Waktu</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deadlines as $d): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark border mb-1"><?= $d['category'] ?></span>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 260px;"><?= htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td><?= FormatHelper::indonesianDate($d['due_date']) ?></td>
                                    <td>
                                        <?php if ($d['days_left'] < 0): ?>
                                            <span class="badge bg-danger text-white">Terlambat <?= abs($d['days_left']) ?> Hari</span>
                                        <?php elseif ($d['days_left'] <= 30): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold"><?= $d['days_left'] ?> Hari Lagi</span>
                                        <?php elseif ($d['days_left'] <= 90): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><?= $d['days_left'] ?> Hari Lagi</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $d['days_left'] ?> Hari</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= $baseUrl ?>/<?= $d['target_url'] ?>" class="btn btn-sm btn-light border py-1">Lihat</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Overdue RTL & Action Items -->
    <div class="col-12 col-xl-5">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-arrow-guide text-danger fs-4"></i> RTL Terlambat & Kritis
                </h5>
                <a href="<?= $baseUrl ?>/meetings/rtl" class="btn btn-sm btn-outline-danger">Semua RTL</a>
            </div>
            <div class="card-body p-4">
                <?php if (empty($overdueRtls)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="ti ti-check fs-2 text-success mb-2 d-block"></i>
                        Tidak ada tindak lanjut rapat yang terlambat.
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($overdueRtls as $rtl): ?>
                            <div class="p-3 bg-danger-subtle border border-danger-subtle rounded-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge bg-danger text-white"><?= $rtl['item_code'] ?></span>
                                    <span class="small text-danger fw-bold">Deadline: <?= FormatHelper::indonesianDate($rtl['deadline']) ?></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($rtl['description'], ENT_QUOTES, 'UTF-8') ?></h6>
                                <div class="d-flex align-items-center justify-content-between small text-muted">
                                    <span>PIC: <strong><?= htmlspecialchars($rtl['pic_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                    <span>Progres: <?= $rtl['progress_percentage'] ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
