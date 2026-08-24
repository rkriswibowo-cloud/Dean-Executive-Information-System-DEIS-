<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Audit Mutu Internal (AMI) Fakultas</h5>
        <a href="<?= $baseUrl ?>/quality" class="btn btn-sm btn-outline-secondary">← Standar SPMI</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Program Studi</th>
                        <th>Tahun & Tanggal</th>
                        <th>Lead Auditor</th>
                        <th>Temuan KTS Major</th>
                        <th>Temuan KTS Minor</th>
                        <th>Observasi (OB)</th>
                        <th class="text-end pe-4">Status Siklus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($audits as $a): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $a['period_year'] ?> (<?= FormatHelper::indonesianDate($a['audit_date']) ?>)</td>
                            <td><?= htmlspecialchars($a['lead_auditor'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-danger"><?= $a['kts_major_count'] ?> KTS Major</span></td>
                            <td><span class="badge bg-warning text-dark"><?= $a['kts_minor_count'] ?> KTS Minor</span></td>
                            <td><span class="badge bg-info"><?= $a['ob_count'] ?> OB</span></td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($a['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Findings List -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3">
        <h5 class="card-title fw-bold mb-0">Daftar Temuan Audit Mutu Internal & Tindak Lanjut</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Tipe Temuan</th>
                        <th>Program Studi</th>
                        <th>Uraian Temuan & Akar Masalah</th>
                        <th>Tindakan Koreksi</th>
                        <th>PIC & Deadline</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($findings as $f): ?>
                        <tr class="<?= $f['finding_type'] === 'KTS Major' ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-<?= $f['finding_type'] === 'KTS Major' ? 'danger' : 'warning' ?> text-<?= $f['finding_type'] === 'KTS Major' ? 'white' : 'dark' ?>">
                                    <?= $f['finding_type'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($f['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="max-width: 260px;">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($f['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($f['root_cause'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td style="max-width: 240px;">
                                <small class="text-dark fw-semibold"><?= htmlspecialchars($f['corrective_action'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($f['pic'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-danger fw-bold">DL: <?= FormatHelper::indonesianDate($f['deadline']) ?></small>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($f['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
