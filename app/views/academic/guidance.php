<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Monitoring Bimbingan (DPA / Skripsi / Magang / MBKM)</h5>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/academic/guidance?type=' + this.value">
                <option value="">Semua Jenis Bimbingan</option>
                <option value="Skripsi" <?= $filterType === 'Skripsi' ? 'selected' : '' ?>>Skripsi / Tugas Akhir</option>
                <option value="MBKM" <?= $filterType === 'MBKM' ? 'selected' : '' ?>>MBKM / MSIB</option>
                <option value="Magang" <?= $filterType === 'Magang' ? 'selected' : '' ?>>Magang Industri</option>
                <option value="DPA" <?= $filterType === 'DPA' ? 'selected' : '' ?>>Dosen Pembimbing Akademik</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Jenis</th>
                        <th>Mahasiswa</th>
                        <th>Judul / Topik Bimbingan</th>
                        <th>Dosen Pembimbing</th>
                        <th>Progres</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Monitoring</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guidances as $g): ?>
                        <tr class="<?= $g['status'] === 'Terlambat' || $g['status'] === 'Bermasalah' ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border"><?= $g['type'] ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($g['student_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= $g['nim'] ?> • <?= $g['program_name'] ?></small>
                            </td>
                            <td style="max-width: 280px;">
                                <div class="small fw-semibold text-dark"><?= htmlspecialchars($g['title'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($g['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <strong><?= $g['progress_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-<?= $g['status'] === 'Terlambat' || $g['status'] === 'Bermasalah' ? 'danger' : 'primary' ?>" style="width: <?= $g['progress_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($g['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalGuidanceAction<?= $g['id'] ?>">
                                    <i class="ti ti-edit me-1"></i> Aksi Dekanat
                                </button>

                                <!-- Modal Intervensi Bimbingan -->
                                <div class="modal fade" id="modalGuidanceAction<?= $g['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-user-check text-primary"></i> Intervensi Bimbingan Dekanat
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/academic/action-guidance" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="guidance_id" value="<?= $g['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($g['student_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $g['nim'] ?>)</div>
                                                        <div class="small text-muted">Pembimbing: <?= htmlspecialchars($g['lecturer_name'], ENT_QUOTES, 'UTF-8') ?> | Jenis: <?= $g['type'] ?></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Status Bimbingan</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="Aktif" <?= $g['status'] === 'Aktif' ? 'selected' : '' ?>>🔵 Aktif (Dalam Proses)</option>
                                                            <option value="Selesai" <?= $g['status'] === 'Selesai' ? 'selected' : '' ?>>✅ Selesai / Siap Sidang</option>
                                                            <option value="Terlambat" <?= $g['status'] === 'Terlambat' ? 'selected' : '' ?>>⚠️ Terlambat (Perlu Surat Peringatan)</option>
                                                            <option value="Bermasalah" <?= $g['status'] === 'Bermasalah' ? 'selected' : '' ?>>❌ Bermasalah (Ganti Pembimbing / Kasus)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Persentase Progres (%)</label>
                                                        <input type="number" name="progress_percentage" class="form-control" min="0" max="100" value="<?= $g['progress_percentage'] ?>" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan Tindakan / Intervensi Dekan</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Terbitkan instruksi penjadwalan ujian proposal / pemanggilan bimbingan intensif..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                        <i class="ti ti-device-floppy me-1"></i> Simpan Tindakan
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
