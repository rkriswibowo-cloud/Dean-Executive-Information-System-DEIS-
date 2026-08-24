<?php use App\Helpers\FormatHelper; ?>

<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="ti ti-certificate text-warning fs-3"></i> Status & Radar Akreditasi Program Studi
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Program Studi</th>
                        <th>Lembaga Akreditasi</th>
                        <th>Peringkat Saat Ini</th>
                        <th>Target Re-Akreditasi</th>
                        <th>Masa Berlaku</th>
                        <th>Sisa Waktu</th>
                        <th>Progres LED & LKPS</th>
                        <th class="text-end pe-4">Status Radar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accreditations as $a): ?>
                        <tr class="<?= $a['status'] === 'Kritis' ? 'table-danger' : ($a['status'] === 'Perhatian' ? 'table-warning' : '') ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $a['degree'] ?>)</div>
                                <small class="text-muted">PIC: <?= htmlspecialchars($a['pic'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $a['institution'] ?></span></td>
                            <td><?= FormatHelper::statusBadge($a['current_grade']) ?></td>
                            <td><strong class="text-primary"><?= $a['target_grade'] ?></strong></td>
                            <td><?= FormatHelper::indonesianDate($a['valid_until']) ?></td>
                            <td>
                                <?php if ($a['calculated_days_remaining'] < 90): ?>
                                    <span class="badge bg-danger text-white fw-bold px-2 py-1">
                                        <i class="ti ti-alert-triangle me-1"></i> <?= $a['calculated_days_remaining'] ?> Hari Lagi
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <?= $a['calculated_days_remaining'] ?> Hari
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 160px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>LED: <strong><?= $a['led_progress'] ?>%</strong></span>
                                    <span>LKPS: <strong><?= $a['lkps_progress'] ?>%</strong></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $a['overall_progress'] >= 90 ? 'success' : ($a['overall_progress'] >= 70 ? 'warning' : 'danger') ?>" style="width: <?= $a['overall_progress'] ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($a['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Gap Analysis & Action Plan Details -->
<div class="row g-4">
    <?php foreach ($accreditations as $a): ?>
        <div class="col-12 col-lg-6">
            <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title fw-bold mb-0"><?= $a['program_name'] ?> - Action Plan & Gap Analysis</h6>
                    <?= FormatHelper::statusBadge($a['status']) ?>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small text-muted text-uppercase">Gap Analysis & Temuan Utama</label>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($a['gap_notes'] ?? 'Tidak ada catatan gap.', ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                    <div>
                        <label class="fw-bold small text-muted text-uppercase">Rencana Aksi Dekanat (Action Plan)</label>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($a['action_plan'] ?? 'Tidak ada rencana aksi tertulis.', ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
