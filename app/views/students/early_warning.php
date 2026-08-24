<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="card-title fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                <i class="ti ti-alert-triangle fs-3"></i> Early Warning System - Mahasiswa Berisiko DO / Kritis
            </h5>
            <small class="text-muted">Deteksi otomatis mahasiswa dengan IPK < 2.0, presensi < 70%, atau masa studi kritis semester 10+.</small>
        </div>
        <a href="<?= $baseUrl ?>/students" class="btn btn-sm btn-outline-secondary">← Semua Mahasiswa</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Tingkat Risiko</th>
                        <th>Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Semester</th>
                        <th>IPK</th>
                        <th>Presensi</th>
                        <th class="pe-4">Indikator Masalah / Alasan Risiko</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($atRiskStudents as $s): ?>
                        <tr class="<?= $s['risk_status'] === 'Critical' ? 'table-danger' : 'table-warning' ?>">
                            <td class="ps-4">
                                <span class="badge bg-<?= $s['risk_status'] === 'Critical' ? 'danger' : 'warning' ?> text-<?= $s['risk_status'] === 'Critical' ? 'white' : 'dark' ?> fw-bold">
                                    <?= strtoupper($s['risk_status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted">NIM: <?= $s['nim'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($s['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Semester <?= $s['semester'] ?></td>
                            <td><strong class="text-danger"><?= $s['current_gpa'] ?></strong></td>
                            <td><strong><?= $s['attendance_percentage'] ?>%</strong></td>
                            <td class="pe-4">
                                <div class="small fw-semibold text-danger"><?= htmlspecialchars($s['risk_reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
