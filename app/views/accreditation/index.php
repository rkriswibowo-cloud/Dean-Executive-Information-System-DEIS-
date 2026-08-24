<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
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
                                <?php elseif ($a['calculated_days_remaining'] < 180): ?>
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                                        <i class="ti ti-clock me-1"></i> <?= $a['calculated_days_remaining'] ?> Hari Lagi
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
                            <td><?= FormatHelper::statusBadge($a['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm btn-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalAccreditation<?= $a['id'] ?>">
                                    <i class="ti ti-edit me-1"></i> Update Progres
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
            <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="card-title fw-bold mb-0 text-dark"><?= $a['program_name'] ?> - Action Plan & Gap Analysis</h6>
                    <div class="d-flex align-items-center gap-2">
                        <?= FormatHelper::statusBadge($a['status']) ?>
                        <button type="button" class="btn btn-xs btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalAccreditation<?= $a['id'] ?>">
                            <i class="ti ti-edit"></i> Edit
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small text-muted text-uppercase d-flex align-items-center gap-1">
                            <i class="ti ti-search text-warning"></i> Gap Analysis & Temuan Utama
                        </label>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($a['gap_notes'] ?? 'Belum ada catatan gap analysis.', ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                    <div>
                        <label class="fw-bold small text-muted text-uppercase d-flex align-items-center gap-1">
                            <i class="ti ti-target-arrow text-primary"></i> Rencana Aksi Dekanat (Action Plan)
                        </label>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($a['action_plan'] ?? 'Belum ada rencana aksi tertulis.', ENT_QUOTES, 'UTF-8')) ?></p>
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
            <div class="modal-header bg-body border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-certificate text-warning"></i> Update Perkembangan & Radar Akreditasi
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/accreditation/update" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <div class="modal-body p-4">
                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($a['program_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $a['degree'] ?>)</div>
                        <div class="small text-muted">Lembaga: <?= $a['institution'] ?> | Masa Berlaku Saat Ini: <?= FormatHelper::indonesianDate($a['valid_until']) ?></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Lembaga Akreditasi</label>
                            <input type="text" name="institution" class="form-control" value="<?= htmlspecialchars($a['institution'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Peringkat Saat Ini</label>
                            <select name="current_grade" class="form-select" required>
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
                            <select name="target_grade" class="form-select" required>
                                <option value="Unggul" <?= $a['target_grade'] === 'Unggul' ? 'selected' : '' ?>>Unggul</option>
                                <option value="Baik Sekali" <?= $a['target_grade'] === 'Baik Sekali' ? 'selected' : '' ?>>Baik Sekali</option>
                                <option value="Internasional" <?= $a['target_grade'] === 'Internasional' ? 'selected' : '' ?>>Akreditasi Internasional</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tanggal Habis Masa Berlaku</label>
                            <input type="date" name="valid_until" class="form-control" value="<?= $a['valid_until'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">PIC / Ketua Taskforce Akreditasi</label>
                            <input type="text" name="pic" class="form-control" value="<?= htmlspecialchars($a['pic'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Progres LED (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="led_progress" class="form-control" value="<?= $a['led_progress'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Progres LKPS (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="lkps_progress" class="form-control" value="<?= $a['lkps_progress'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Status Radar</label>
                            <select name="status" class="form-select" required>
                                <option value="Aman" <?= $a['status'] === 'Aman' ? 'selected' : '' ?>>🟢 Aman (> 180 Hari)</option>
                                <option value="Perhatian" <?= $a['status'] === 'Perhatian' ? 'selected' : '' ?>>🟡 Perhatian (90 - 180 Hari)</option>
                                <option value="Kritis" <?= $a['status'] === 'Kritis' ? 'selected' : '' ?>>🔴 Kritis (< 90 Hari)</option>
                                <option value="Expired" <?= $a['status'] === 'Expired' ? 'selected' : '' ?>>❌ Kadaluarsa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Gap Analysis & Temuan Utama (Evaluasi Diri)</label>
                        <textarea name="gap_notes" class="form-control" rows="3" placeholder="Masukkan poin-poin gap kriteria akreditasi yang masih kurang (misal rasio dosen, luaran penelitian, dll)..."><?= htmlspecialchars($a['gap_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Rencana Aksi Dekanat (Action Plan)</label>
                        <textarea name="action_plan" class="form-control" rows="3" placeholder="Langkah-langkah strategis dan timeline percepatan finalisasi dokumen..."><?= htmlspecialchars($a['action_plan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Perkembangan Akreditasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
