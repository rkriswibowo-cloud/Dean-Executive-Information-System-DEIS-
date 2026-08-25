<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Rata-rata Capaian IKU & Renstra</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $avgAchievement ?>%</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Jumlah Indikator Dinamis</span>
            <h3 class="fw-bold mb-0 mt-1"><?= count($indicators) ?> <span class="fs-6 fw-normal text-muted">Indikator</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Program Strategis Renstra</span>
            <h3 class="fw-bold mb-0 mt-1"><?= count($renstraPrograms) ?> <span class="fs-6 fw-normal text-muted">Program</span></h3>
        </div>
    </div>
</div>

<!-- 1. Indikator Kinerja Utama (IKU & Renstra) Table -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Capaian Target vs Realisasi Indikator Strategis (2026)</h5>
        <a href="<?= $baseUrl ?>/strategic/indicators" class="btn btn-sm btn-outline-secondary">Master Indikator Dinamis</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Indikator Strategis</th>
                        <th>Kategori</th>
                        <th>Target (2026)</th>
                        <th>Realisasi Saat Ini</th>
                        <th>Capaian (%)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($indicators as $ind): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="max-width: 280px;">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($ind['formula'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $ind['category'] ?></span></td>
                            <td><strong><?= $ind['target_value'] ?></strong> <?= $ind['unit'] ?></td>
                            <td><strong class="text-primary"><?= $ind['realization_value'] ?? 0 ?></strong> <?= $ind['unit'] ?></td>
                            <td>
                                <?php 
                                $ach = min(100.0, (float)($ind['achievement_percentage'] ?? 0)); 
                                $isFull = ($ach >= 100.0);
                                ?>
                                <span class="badge <?= $isFull ? 'bg-success-subtle text-success border border-success-subtle' : ($ach >= 80 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') ?> font-monospace fw-bold px-2 py-0.5" style="font-size: 0.8rem;">
                                    <?= $ach ?>%
                                </span>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar <?= $isFull ? 'bg-success' : ($ach >= 80 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $ach ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($ind['realization_status'] ?? 'Proses') ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalRealization<?= $ind['id'] ?>" title="Input Realisasi Capaian">
                                        <i class="ti ti-edit"></i> Realisasi
                                    </button>
                                </div>

                                <!-- Modal Update Realization -->
                                <div class="modal fade" id="modalRealization<?= $ind['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow text-start">
                                            <form action="<?= $baseUrl ?>/strategic/realization" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="target_id" value="<?= $ind['target_id'] ?? $ind['id'] ?>">
                                                <input type="hidden" name="target_value" value="<?= $ind['target_value'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Update Realisasi: <?= $ind['code'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Target (<?= $ind['unit'] ?>)</label>
                                                        <input type="text" class="form-control" value="<?= $ind['target_value'] ?> <?= $ind['unit'] ?>" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Nilai Realisasi Baru (<?= $ind['unit'] ?>)</label>
                                                        <input type="number" step="0.01" class="form-control" name="realization_value" value="<?= $ind['realization_value'] ?? '' ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan Verifikasi</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan capaian atau eviden..."><?= htmlspecialchars($ind['realization_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Realisasi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. Renstra Strategic Programs -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3">
        <h5 class="card-title fw-bold mb-0">Program Strategis Rencana Strategis (Renstra) Fakultas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Sasaran Strategis</th>
                        <th>Nama Program Kerja</th>
                        <th>PIC</th>
                        <th>Pagu Anggaran</th>
                        <th>Periode</th>
                        <th>Progres</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($renstraPrograms as $prog): ?>
                        <tr>
                            <td class="ps-4 fw-semibold text-dark" style="max-width: 220px;"><?= htmlspecialchars($prog['strategic_objective'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-bold text-dark" style="max-width: 240px;"><?= htmlspecialchars($prog['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($prog['pic'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= FormatHelper::currency($prog['budget']) ?></td>
                            <td><?= $prog['start_year'] ?> - <?= $prog['end_year'] ?></td>
                            <td style="min-width: 120px;">
                                <strong><?= $prog['progress_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: <?= $prog['progress_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($prog['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
