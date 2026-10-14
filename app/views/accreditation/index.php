<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;

$unggulCount = 0;
$kritisCount = 0;
$avgOverallProgress = 0;
$totalAcc = count($accreditations);

if ($totalAcc > 0) {
    $sumProg = 0;
    foreach ($accreditations as $a) {
        if (($a['current_grade'] ?? '') === 'Unggul' || ($a['current_grade'] ?? '') === 'A') {
            $unggulCount++;
        }
        if (($a['status'] ?? '') === 'Kritis' || ($a['status'] ?? '') === 'Perhatian') {
            $kritisCount++;
        }
        $sumProg += ($a['overall_progress'] ?? 0);
    }
    $avgOverallProgress = round($sumProg / $totalAcc, 1);
}
?>

<!-- Executive KPI Summary for Accreditation -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Prodi Akreditasi Unggul / A</div>
                <div class="kpi-value text-success"><?= $unggulCount ?> <span class="fs-6 text-muted fw-normal">/ <?= $totalAcc ?> Prodi</span></div>
                <div class="small text-muted mt-1"><i class="fa-solid fa-award text-success me-1"></i> Standar mutu tertinggi BAN-PT / LAM</div>
            </div>
            <div class="kpi-icon-wrap success">
                <i class="fa-solid fa-medal"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Rata-rata Kesiapan Dokumen</div>
                <div class="kpi-value text-primary"><?= $avgOverallProgress ?>%</div>
                <div class="small text-muted mt-1"><i class="fa-solid fa-file-shield text-primary me-1"></i> Rerata progres LED & LKPS</div>
            </div>
            <div class="kpi-icon-wrap primary">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Perlu Tindakan Khusus</div>
                <div class="kpi-value <?= $kritisCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $kritisCount ?> <span class="fs-6 text-muted fw-normal">Prodi</span></div>
                <div class="small text-muted mt-1">
                    <span class="badge <?= $kritisCount > 0 ? 'badge-soft-danger' : 'badge-soft-success' ?>">
                        <i class="fa-solid <?= $kritisCount > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-1"></i>
                        <?= $kritisCount > 0 ? 'Masa berlaku < 180 hari' : 'Semua prodi aman' ?>
                    </span>
                </div>
            </div>
            <div class="kpi-icon-wrap <?= $kritisCount > 0 ? 'danger' : 'success' ?>">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-award text-warning"></i> Status & Radar Akreditasi Program Studi
        </h5>
        <span class="badge badge-soft-warning px-3 py-2">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> Pemantauan Real-time Siklus Akreditasi
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Program Studi</th>
                        <th>Lembaga</th>
                        <th>Peringkat Saat Ini</th>
                        <th>Target Re-Akreditasi</th>
                        <th>Masa Berlaku</th>
                        <th>Sisa Waktu</th>
                        <th>Progres LED & LKPS</th>
                        <th>Status Radar</th>
                        <th class="text-end pe-4">Aksi Perkembangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accreditations as $a): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-body fs-6"><?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?> <span class="badge badge-soft-secondary font-monospace ms-1"><?= $a['degree'] ?></span></div>
                                <small class="text-muted"><i class="fa-regular fa-user me-1"></i> PIC: <?= htmlspecialchars($a['pic'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><span class="badge badge-soft-info border-0"><?= htmlspecialchars($a['institution'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= FormatHelper::statusBadge($a['current_grade']) ?></td>
                            <td><strong class="text-primary"><?= $a['target_grade'] ?></strong></td>
                            <td><?= FormatHelper::indonesianDate($a['valid_until']) ?></td>
                            <td>
                                <?php if ($a['calculated_days_remaining'] < 90): ?>
                                    <span class="badge badge-soft-danger fw-bold px-2 py-1">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $a['calculated_days_remaining'] ?> Hari
                                    </span>
                                <?php elseif ($a['calculated_days_remaining'] < 180): ?>
                                    <span class="badge badge-soft-warning fw-bold px-2 py-1">
                                        <i class="fa-solid fa-clock me-1"></i> <?= $a['calculated_days_remaining'] ?> Hari
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-soft-success fw-bold px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> <?= $a['calculated_days_remaining'] ?> Hari
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 160px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>LED: <strong class="text-body"><?= $a['led_progress'] ?>%</strong></span>
                                    <span>LKPS: <strong class="text-body"><?= $a['lkps_progress'] ?>%</strong></span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div class="progress-bar bg-<?= $a['overall_progress'] >= 90 ? 'success' : ($a['overall_progress'] >= 70 ? 'warning' : 'danger') ?>" style="width: <?= $a['overall_progress'] ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($a['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm btn-soft-primary rounded-3 px-3 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAccreditation<?= $a['id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> <span>Update</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Gap Analysis & Action Plan Details Cards -->
<div class="row g-4 mb-4">
    <?php foreach ($accreditations as $a): ?>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
                        <i class="fa-solid fa-list-check text-primary"></i> <?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <?= FormatHelper::statusBadge($a['status']) ?>
                        <button type="button" class="btn btn-sm btn-soft-primary rounded-3 px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalAccreditation<?= $a['id'] ?>" title="Edit Rencana Aksi">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small text-muted text-uppercase d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-magnifying-glass-chart text-warning"></i> Gap Analysis & Temuan Utama
                        </label>
                        <div class="small text-body mb-0 bg-body-tertiary p-3 rounded-3 border-0">
                            <?= nl2br(htmlspecialchars($a['gap_notes'] ?? 'Belum ada catatan gap analysis.', ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                    <div>
                        <label class="fw-bold small text-muted text-uppercase d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-bullseye text-primary"></i> Rencana Aksi Dekanat (Action Plan)
                        </label>
                        <div class="small text-body mb-0 bg-body-tertiary p-3 rounded-3 border-0">
                            <?= nl2br(htmlspecialchars($a['action_plan'] ?? 'Belum ada rencana aksi tertulis.', ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modals for Updating Accreditation Progress -->
<?php foreach ($accreditations as $a): ?>
<div class="modal fade" id="modalAccreditation<?= $a['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                <h6 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-award text-warning"></i> Update Perkembangan & Radar Akreditasi
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/accreditation/update" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <div class="modal-body p-4">
                    <div class="bg-body-secondary bg-opacity-50 p-3 rounded-3 mb-3">
                        <div class="fw-bold text-body fs-6"><?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $a['degree'] ?>)</div>
                        <div class="small text-muted">Lembaga: <?= htmlspecialchars($a['institution'], ENT_QUOTES, 'UTF-8') ?> &bull; Masa Berlaku Saat Ini: <?= FormatHelper::indonesianDate($a['valid_until']) ?></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Lembaga Akreditasi</label>
                            <input type="text" name="institution" class="form-control rounded-3" value="<?= htmlspecialchars($a['institution'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Peringkat Saat Ini</label>
                            <select name="current_grade" class="form-select rounded-3" required>
                                <option value="Unggul" <?= $a['current_grade'] === 'Unggul' ? 'selected' : '' ?>>Unggul</option>
                                <option value="Baik Sekali" <?= $a['current_grade'] === 'Baik Sekali' ? 'selected' : '' ?>>Baik Sekali</option>
                                <option value="Baik" <?= $a['current_grade'] === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                <option value="A" <?= $a['current_grade'] === 'A' ? 'selected' : '' ?>>A (BAN-PT)</option>
                                <option value="B" <?= $a['current_grade'] === 'B' ? 'selected' : '' ?>>B (BAN-PT)</option>
                                <option value="C" <?= $a['current_grade'] === 'C' ? 'selected' : '' ?>>C (BAN-PT)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Target Re-Akreditasi</label>
                            <select name="target_grade" class="form-select rounded-3" required>
                                <option value="Unggul" <?= $a['target_grade'] === 'Unggul' ? 'selected' : '' ?>>Unggul</option>
                                <option value="Baik Sekali" <?= $a['target_grade'] === 'Baik Sekali' ? 'selected' : '' ?>>Baik Sekali</option>
                                <option value="Internasional" <?= $a['target_grade'] === 'Internasional' ? 'selected' : '' ?>>Akreditasi Internasional</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tanggal Habis Masa Berlaku</label>
                            <input type="date" name="valid_until" class="form-control rounded-3" value="<?= $a['valid_until'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">PIC / Ketua Taskforce Akreditasi</label>
                            <input type="text" name="pic" class="form-control rounded-3" value="<?= htmlspecialchars($a['pic'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Progres LED (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="led_progress" class="form-control rounded-3" value="<?= $a['led_progress'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Progres LKPS (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="lkps_progress" class="form-control rounded-3" value="<?= $a['lkps_progress'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Status Radar</label>
                            <select name="status" class="form-select rounded-3" required>
                                <option value="Aman" <?= $a['status'] === 'Aman' ? 'selected' : '' ?>>Aman (&gt; 180 Hari)</option>
                                <option value="Perhatian" <?= $a['status'] === 'Perhatian' ? 'selected' : '' ?>>Perhatian (90 - 180 Hari)</option>
                                <option value="Kritis" <?= $a['status'] === 'Kritis' ? 'selected' : '' ?>>Kritis (&lt; 90 Hari)</option>
                                <option value="Expired" <?= $a['status'] === 'Expired' ? 'selected' : '' ?>>Kadaluarsa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Gap Analysis & Temuan Utama (Evaluasi Diri)</label>
                        <textarea name="gap_notes" class="form-control rounded-3" rows="3" placeholder="Masukkan poin-poin gap kriteria akreditasi yang masih kurang (misal rasio dosen, luaran penelitian, dll)..."><?= htmlspecialchars($a['gap_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Rencana Aksi Dekanat (Action Plan)</label>
                        <textarea name="action_plan" class="form-control rounded-3" rows="3" placeholder="Langkah-langkah strategis dan timeline percepatan finalisasi dokumen..."><?= htmlspecialchars($a['action_plan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perkembangan Akreditasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

