<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Struktur Kurikulum & Kesiapan RPS</h5>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/academic/courses?program_id=' + this.value">
                <option value="">Semua Program Studi</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Semester</th>
                        <th>Dosen Koordinator</th>
                        <th>Prodi</th>
                        <th>Status RPS</th>
                        <th class="text-end pe-4">Aksi Pengesahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $c['sks'] ?> SKS</td>
                            <td>Semester <?= $c['semester'] ?></td>
                            <td><?= htmlspecialchars($c['lecturer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= FormatHelper::statusBadge($c['rps_status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalRpsAction<?= $c['id'] ?>">
                                    <i class="ti ti-certificate me-1"></i> Validasi RPS
                                </button>

                                <!-- Modal Validasi RPS -->
                                <div class="modal fade" id="modalRpsAction<?= $c['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-certificate text-primary"></i> Pengesahan & Validasi RPS Dekanat
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/academic/action-rps" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?> (<?= $c['sks'] ?> SKS)</div>
                                                        <div class="small text-muted">Koordinator: <?= htmlspecialchars($c['lecturer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?> | Prodi: <?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Keputusan Status RPS</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="Lengkap" <?= $c['rps_status'] === 'Lengkap' ? 'selected' : '' ?>>✅ Sahkan & Setujui (Lengkap)</option>
                                                            <option value="Revisi" <?= $c['rps_status'] === 'Revisi' ? 'selected' : '' ?>>⚠️ Minta Perbaikan / Revisi RPS</option>
                                                            <option value="Belum Lengkap" <?= $c['rps_status'] === 'Belum Lengkap' ? 'selected' : '' ?>>❌ Belum Lengkap / Dokumen Kurang</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan Evaluasi Kurikulum / RPS Dekan</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan catatan pengesahan atau bagian yang perlu disesuaikan dengan CPL prodi..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                        <i class="ti ti-check me-1"></i> Simpan Pengesahan
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
