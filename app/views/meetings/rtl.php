<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-body mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check text-info"></i> Monitoring & Tracking RTL Rapat (Tindak Lanjut)
        </h4>
        <p class="text-muted small mb-0">Pastikan seluruh keputusan rapat Dekanat tereksekusi, terukur, dan terdokumentasi sampai tuntas.</p>
    </div>
    <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Rapat
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-primary"></i> Daftar Rencana Tindak Lanjut (RTL) Aktif & Selesai
        </h5>
        <span class="badge badge-soft-primary px-3 py-2"><?= count($actionItems) ?> Butir RTL</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
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
                        <tr>
                            <td class="ps-4">
                                <span class="badge badge-soft-secondary font-monospace mb-1"><?= htmlspecialchars($item['item_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div><?= FormatHelper::priorityBadge($item['priority']) ?></div>
                            </td>
                            <td style="max-width: 260px;">
                                <div class="fw-bold text-body"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($item['notes'])): ?>
                                    <small class="text-muted"><i class="fa-solid fa-note-sticky text-warning me-1"></i><?= htmlspecialchars($item['notes'], ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="fw-semibold text-muted text-truncate d-block" style="max-width: 160px;" title="<?= htmlspecialchars($item['meeting_title'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($item['meeting_title'], ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-body"><?= htmlspecialchars($item['pic_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($item['program_name'] ?? 'Fakultas', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div class="fw-bold <?= $item['days_left'] < 0 && $item['status'] !== 'Selesai' ? 'text-danger' : 'text-body' ?>">
                                    <?= FormatHelper::indonesianDate($item['deadline']) ?>
                                </div>
                                <?php if ($item['status'] !== 'Selesai'): ?>
                                    <small class="<?= $item['days_left'] < 0 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                        <?= $item['days_left'] < 0 ? 'Terlambat ' . abs($item['days_left']) . ' hari' : $item['days_left'] . ' hari lagi' ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 120px;">
                                <strong class="text-body"><?= $item['progress_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 5px; border-radius: 4px;">
                                    <div class="progress-bar bg-<?= $item['status'] === 'Selesai' ? 'success' : ($item['status'] === 'Terlambat' ? 'danger' : 'primary') ?>" style="width: <?= $item['progress_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($item['status']) ?></td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-soft-primary rounded-3 px-3 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalRtl<?= $item['id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> <span>Update</span>
                                </button>

                                <!-- Modal Update RTL -->
                                <div class="modal fade" id="modalRtl<?= $item['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                                            <form action="<?= $baseUrl ?>/meetings/rtl-update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="rtl_id" value="<?= $item['id'] ?>">
                                                <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                                                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i> Update RTL: <?= htmlspecialchars($item['item_code'], ENT_QUOTES, 'UTF-8') ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Uraian Tindak Lanjut</label>
                                                        <div class="small text-body bg-body-tertiary p-3 rounded-3"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Status RTL</label>
                                                        <select name="status" class="form-select rounded-3">
                                                            <option value="Belum Mulai" <?= $item['status'] === 'Belum Mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                                                            <option value="Proses" <?= $item['status'] === 'Proses' ? 'selected' : '' ?>>Proses Pengerjaan</option>
                                                            <option value="Diserahkan" <?= $item['status'] === 'Diserahkan' ? 'selected' : '' ?>>Diserahkan (Menunggu Verifikasi)</option>
                                                            <option value="Selesai" <?= $item['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai &amp; Diverifikasi</option>
                                                            <option value="Terlambat" <?= $item['status'] === 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                                            <option value="Dibatalkan" <?= $item['status'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Progres Capaian (%)</label>
                                                        <input type="number" min="0" max="100" class="form-control rounded-3" name="progress_percentage" value="<?= $item['progress_percentage'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Catatan / Bukti Penyelesaian</label>
                                                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Tambahkan catatan perkembangan atau link eviden..."><?= htmlspecialchars($item['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Progres RTL
                                                    </button>
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

