<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-arrow-guide text-info fs-2"></i> Monitoring & Tracking RTL Rapat (Tindak Lanjut)
        </h3>
        <p class="text-muted small mb-0">Pastikan seluruh keputusan rapat Dekanat tereksekusi, terukur, dan terdokumentasi sampai tuntas.</p>
    </div>
    <a href="<?= $baseUrl ?>/meetings" class="btn btn-outline-secondary">
        <i class="ti ti-calendar me-1"></i> Kembali ke Daftar Rapat
    </a>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Daftar Rencana Tindak Lanjut (RTL) Aktif & Selesai</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($actionItems) ?> Butir RTL</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode & Prioritas</th>
                        <th>Uraian Tindak Lanjut</th>
                        <th>Rapat Asal</th>
                        <th>PIC / Penanggung Jawab</th>
                        <th>Batas Waktu</th>
                        <th>Progres</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actionItems as $item): ?>
                        <tr class="<?= $item['status'] === 'Terlambat' ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= $item['item_code'] ?></span>
                                <div><?= FormatHelper::priorityBadge($item['priority']) ?></div>
                            </td>
                            <td style="max-width: 260px;">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($item['notes'])): ?>
                                    <small class="text-muted"><i class="ti ti-notes me-1"></i><?= htmlspecialchars($item['notes'], ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="fw-semibold text-muted text-truncate d-block" style="max-width: 160px;">
                                    <?= htmlspecialchars($item['meeting_title'], ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($item['pic_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($item['program_name'] ?? 'Fakultas', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div class="fw-bold <?= $item['days_left'] < 0 && $item['status'] !== 'Selesai' ? 'text-danger' : '' ?>">
                                    <?= FormatHelper::indonesianDate($item['deadline']) ?>
                                </div>
                                <?php if ($item['status'] !== 'Selesai'): ?>
                                    <small class="text-muted"><?= $item['days_left'] < 0 ? 'Terlambat ' . abs($item['days_left']) . ' hari' : $item['days_left'] . ' hari lagi' ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= $item['progress_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-<?= $item['status'] === 'Selesai' ? 'success' : ($item['status'] === 'Terlambat' ? 'danger' : 'primary') ?>" style="width: <?= $item['progress_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($item['status']) ?></td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary py-1" data-bs-toggle="modal" data-bs-target="#modalRtl<?= $item['id'] ?>">
                                    <i class="ti ti-edit"></i> Update
                                </button>

                                <!-- Modal Update RTL -->
                                <div class="modal fade" id="modalRtl<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow text-start">
                                            <form action="<?= $baseUrl ?>/meetings/rtl-update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="rtl_id" value="<?= $item['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Update RTL: <?= $item['item_code'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Uraian Tindak Lanjut</label>
                                                        <p class="small text-muted mb-0 bg-light p-2 rounded-2"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Status RTL</label>
                                                        <select name="status" class="form-select">
                                                            <option value="Belum Mulai" <?= $item['status'] === 'Belum Mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                                                            <option value="Proses" <?= $item['status'] === 'Proses' ? 'selected' : '' ?>>Proses Pengerjaan</option>
                                                            <option value="Diserahkan" <?= $item['status'] === 'Diserahkan' ? 'selected' : '' ?>>Diserahkan (Menunggu Verifikasi)</option>
                                                            <option value="Selesai" <?= $item['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai & Diverifikasi</option>
                                                            <option value="Terlambat" <?= $item['status'] === 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                                            <option value="Dibatalkan" <?= $item['status'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Progres Capaian (%)</label>
                                                        <input type="number" min="0" max="100" class="form-control" name="progress_percentage" value="<?= $item['progress_percentage'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan / Bukti Penyelesaian</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan perkembangan atau link eviden..."><?= htmlspecialchars($item['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Progres RTL</button>
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
