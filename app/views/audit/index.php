<?php use App\Helpers\FormatHelper; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-history text-primary fs-2"></i> Audit Trail & Log Aktivitas Sistem
        </h3>
        <p class="text-muted small mb-0">Seluruh riwayat aksi kritis, login, perubahan data, dan approval tercatat dan tidak dapat dimanipulasi.</p>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Riwayat Audit Aktivitas (150 Terakhir)</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/audit?action=' + this.value">
                <option value="">Semua Aksi</option>
                <option value="LOGIN" <?= $filterAction === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                <option value="UPDATE" <?= $filterAction === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                <option value="CREATE" <?= $filterAction === 'CREATE' ? 'selected' : '' ?>>CREATE</option>
                <option value="APPROVE" <?= $filterAction === 'APPROVE' ? 'selected' : '' ?>>APPROVE</option>
                <option value="EXPORT" <?= $filterAction === 'EXPORT' ? 'selected' : '' ?>>EXPORT</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                                <div class="fw-bold text-dark"><?= FormatHelper::indonesianDate($log['created_at'], true) ?></div>
                                <small class="text-muted"><?= FormatHelper::timeAgo($log['created_at']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">@<?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= in_array($log['action'], ['DELETE', 'REJECT']) ? 'danger' : ($log['action'] === 'APPROVE' ? 'success' : ($log['action'] === 'UPDATE' ? 'warning' : 'primary')) ?>-subtle text-dark border px-2 py-1">
                                    <?= $log['action'] ?>
                                </span>
                            </td>
                            <td><strong class="text-dark"><?= htmlspecialchars($log['module'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($log['record_id'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="max-width: 320px;">
                                <?php if (!empty($log['new_values'])): ?>
                                    <pre class="mb-0 bg-light p-2 rounded small text-dark" style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;"><code><?= htmlspecialchars($log['new_values'], ENT_QUOTES, 'UTF-8') ?></code></pre>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <small class="text-muted"><?= $log['ip_address'] ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
