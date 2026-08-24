<?php use App\Helpers\FormatHelper; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Mahasiswa Aktif</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $totalStudents ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-danger">Risiko Kritis (Critical)</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= $criticalCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-warning">Perlu Perhatian (Warning)</span>
            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $warningCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Penerima Beasiswa</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $scholarshipCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Database Mahasiswa & Status Akademik</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/students/early-warning" class="btn btn-sm btn-outline-danger">
                <i class="ti ti-alert-triangle me-1"></i> Early Warning System
            </a>
            <a href="<?= $baseUrl ?>/students/alumni" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-school me-1"></i> Tracer Study & Alumni
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">NIM & Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Angkatan & Sem</th>
                        <th>IPK Kumulatif</th>
                        <th>SKS Selesai</th>
                        <th>Presensi</th>
                        <th>Beasiswa / Prestasi</th>
                        <th class="text-end pe-4">Status Risiko</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr class="<?= $s['risk_status'] === 'Critical' ? 'table-danger' : ($s['risk_status'] === 'Warning' ? 'table-warning' : '') ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted">NIM: <?= $s['nim'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($s['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Angkatan <?= $s['batch_year'] ?> (Sem <?= $s['semester'] ?>)</td>
                            <td><strong><?= $s['current_gpa'] ?></strong> / 4.00</td>
                            <td><?= $s['credits_earned'] ?> SKS</td>
                            <td><?= $s['attendance_percentage'] ?>%</td>
                            <td>
                                <?php if (!empty($s['scholarship'])): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $s['scholarship'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($s['organization'])): ?>
                                    <small class="d-block text-muted"><?= $s['organization'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($s['risk_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
