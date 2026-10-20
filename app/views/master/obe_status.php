<?php
$status = $report['status'] ?? 'not_ready';
$statusMeta = match ($status) {
    'ready' => ['success', 'Siap Digunakan', 'fa-circle-check'],
    'ready_with_warnings' => ['warning', 'Siap dengan Catatan', 'fa-triangle-exclamation'],
    default => ['danger', 'Belum Siap', 'fa-circle-xmark'],
};
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-4 text-white bg-<?= $statusMeta[0] ?>">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <div class="small text-uppercase fw-bold opacity-75 mb-1">Fase 8 &middot; Operational Readiness</div>
                <h3 class="fw-bold text-white mb-1"><i class="fa-solid <?= $statusMeta[2] ?> me-2"></i><?= $statusMeta[1] ?></h3>
                <div class="opacity-75">Diperiksa <?= htmlspecialchars((string)$report['checked_at']) ?></div>
            </div>
            <a href="<?= $baseUrl ?>/master/obe" class="btn btn-light fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i>Kembali ke Master OBE
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
            <div class="text-muted small text-uppercase fw-bold">Kegagalan Kritis</div>
            <div class="display-6 fw-bold text-<?= (int)$report['critical_failures'] > 0 ? 'danger' : 'success' ?>"><?= (int)$report['critical_failures'] ?></div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
            <div class="text-muted small text-uppercase fw-bold">Catatan Tindak Lanjut</div>
            <div class="display-6 fw-bold text-<?= (int)$report['warnings'] > 0 ? 'warning' : 'success' ?>"><?= (int)$report['warnings'] ?></div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent px-4 py-3">
        <h5 class="fw-bold mb-0">Checklist Integrasi OBE</h5>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($report['checks'] as $check): ?>
            <?php
            $meta = match ($check['status']) {
                'passed' => ['success', 'fa-circle-check', 'Lulus'],
                'warning' => ['warning', 'fa-triangle-exclamation', 'Catatan'],
                default => ['danger', 'fa-circle-xmark', 'Gagal'],
            };
            ?>
            <div class="list-group-item px-4 py-3">
                <div class="d-flex align-items-start gap-3">
                    <i class="fa-solid <?= $meta[1] ?> text-<?= $meta[0] ?> mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold"><?= htmlspecialchars($check['name']) ?></span>
                            <span class="badge bg-<?= $meta[0] ?>-subtle text-<?= $meta[0] ?> border border-<?= $meta[0] ?>-subtle"><?= $meta[2] ?></span>
                            <?php if ($check['critical']): ?><span class="badge bg-light text-dark border">Kritis</span><?php endif; ?>
                        </div>
                        <div class="small text-muted mt-1"><?= htmlspecialchars($check['detail']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
