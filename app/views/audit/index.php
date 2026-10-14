<?php use App\Helpers\FormatHelper; ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-body mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-primary"></i> Audit Trail & Log Aktivitas Sistem
        </h4>
        <p class="text-muted small mb-0">Seluruh riwayat aksi kritis, otentikasi login, mutasi data, dan approval tercatat secara immutable.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-primary"></i> Riwayat Audit Aktivitas (150 Terakhir)
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted fw-semibold">Filter:</span>
            <select class="form-select form-select-sm rounded-3" style="width: auto;" onchange="location.href='<?= $baseUrl ?>/audit?action=' + this.value">
                <option value="">Semua Aksi</option>
                <option value="LOGIN" <?= ($filterAction ?? '') === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                <option value="UPDATE" <?= ($filterAction ?? '') === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                <option value="CREATE" <?= ($filterAction ?? '') === 'CREATE' ? 'selected' : '' ?>>CREATE</option>
                <option value="APPROVE" <?= ($filterAction ?? '') === 'APPROVE' ? 'selected' : '' ?>>APPROVE</option>
                <option value="EXPORT" <?= ($filterAction ?? '') === 'EXPORT' ? 'selected' : '' ?>>EXPORT</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="15">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>ID Rekord</th>
                        <th>Data Perubahan (Diff / Payload)</th>
                        <th class="text-end pe-4">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <div class="fw-bold text-body"><?= FormatHelper::indonesianDate($log['created_at'], true) ?></div>
                                <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= FormatHelper::timeAgo($log['created_at']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary font-monospace"><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'badge-soft-primary';
                                if (in_array($log['action'], ['DELETE', 'REJECT'])) {
                                    $badgeClass = 'badge-soft-danger';
                                } elseif ($log['action'] === 'APPROVE') {
                                    $badgeClass = 'badge-soft-success';
                                } elseif ($log['action'] === 'UPDATE') {
                                    $badgeClass = 'badge-soft-warning';
                                } elseif ($log['action'] === 'LOGIN') {
                                    $badgeClass = 'badge-soft-info';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?> font-monospace px-2 py-1">
                                    <?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><strong class="text-body"><?= htmlspecialchars($log['module'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><span class="font-monospace text-muted"><?= htmlspecialchars($log['record_id'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td style="max-width: 320px;">
                                <?php if (!empty($log['new_values'])): ?>
                                    <pre class="mb-0 bg-body-tertiary p-2 rounded-3 text-body" style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;"><code><?= htmlspecialchars($log['new_values'], ENT_QUOTES, 'UTF-8') ?></code></pre>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <span class="badge badge-soft-secondary font-monospace"><i class="fa-solid fa-network-wired me-1"></i><?= htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

