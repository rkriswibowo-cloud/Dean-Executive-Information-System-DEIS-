<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Pagu Anggaran RKA (<?= $selectedYear ?? 2026 ?>)</span>
            <h3 class="fw-bold mb-0 mt-1"><?= FormatHelper::currency($totalBudget) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Realisasi Belanja</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= FormatHelper::currency($totalRealized) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Tingkat Serapan Anggaran</span>
            <h3 class="fw-bold mb-0 mt-1 <?= $overallAbsorption >= 85 ? 'text-success' : ($overallAbsorption >= 60 ? 'text-warning' : 'text-danger') ?>"><?= $overallAbsorption ?>%</h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark">Rincian Anggaran RKA & Evaluasi Serapan Fakultas</h5>
            <span class="text-muted small">Tahun Anggaran <?= $selectedYear ?? 2026 ?></span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddFinance">
                <i class="ti ti-plus me-1"></i> Tambah Pos Anggaran
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kategori & Pos Anggaran</th>
                        <th>Program Studi / Unit</th>
                        <th>Pagu Anggaran</th>
                        <th>Realisasi Belanja</th>
                        <th>Serapan</th>
                        <th>Status Efisiensi</th>
                        <th class="text-end pe-4">Aksi Evaluasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finances as $f): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= $f['category'] ?></span>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><?= htmlspecialchars($f['program_name'] ?? 'Dekanat Fakultas', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= FormatHelper::currency($f['budgeted_amount']) ?></strong></td>
                            <td><strong class="text-primary"><?= FormatHelper::currency($f['realized_amount']) ?></strong></td>
                            <td style="min-width: 140px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><strong><?= $f['absorption_percentage'] ?>%</strong></span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-<?= $f['absorption_percentage'] >= 85 ? 'success' : ($f['absorption_percentage'] >= 60 ? 'warning' : 'danger') ?>" style="width: <?= min(100, $f['absorption_percentage']) ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($f['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditFinance<?= $f['id'] ?>" title="Update Realisasi & Evaluasi">
                                        <i class="ti ti-cash"></i> Aksi Realisasi
                                    </button>
                                    <form action="<?= $baseUrl ?>/finances/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pos anggaran <?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-square" title="Hapus Pos Anggaran">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit & Aksi Realisasi Anggaran -->
                                <div class="modal fade" id="modalEditFinance<?= $f['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-report-money text-primary"></i> Evaluasi & Update Realisasi Anggaran
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/finances/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <input type="hidden" name="fiscal_year" value="<?= $f['fiscal_year'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Nama Pos Anggaran / Kegiatan</label>
                                                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Kategori Anggaran</label>
                                                            <select name="category" class="form-select" required>
                                                                <option value="RKA Operasional" <?= $f['category'] === 'RKA Operasional' ? 'selected' : '' ?>>RKA Operasional</option>
                                                                <option value="RKA Pengembangan" <?= $f['category'] === 'RKA Pengembangan' ? 'selected' : '' ?>>RKA Pengembangan</option>
                                                                <option value="Penelitian" <?= $f['category'] === 'Penelitian' ? 'selected' : '' ?>>Penelitian Dosen</option>
                                                                <option value="PkM" <?= $f['category'] === 'PkM' ? 'selected' : '' ?>>Pengabdian Masyarakat (PkM)</option>
                                                                <option value="Kemahasiswaan" <?= $f['category'] === 'Kemahasiswaan' ? 'selected' : '' ?>>Kegiatan Kemahasiswaan & Prestasi</option>
                                                                <option value="Pendapatan" <?= $f['category'] === 'Pendapatan' ? 'selected' : '' ?>>Pendapatan / Kerja Sama</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Unit / Program Studi</label>
                                                            <select name="study_program_id" class="form-select">
                                                                <option value="0">Dekanat Fakultas</option>
                                                                <?php foreach ($programs as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= ($f['study_program_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Pagu Anggaran Disetujui (Rp)</label>
                                                            <input type="number" step="1000" min="0" class="form-control" name="budgeted_amount" value="<?= $f['budgeted_amount'] ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Total Realisasi Belanja (Rp)</label>
                                                            <input type="number" step="1000" min="0" class="form-control" name="realized_amount" value="<?= $f['realized_amount'] ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                        <i class="ti ti-device-floppy me-1"></i> Simpan Evaluasi Anggaran
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

<!-- Modal Tambah Pos Anggaran -->
<div class="modal fade" id="modalAddFinance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= $baseUrl ?>/finances/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="fiscal_year" value="<?= $selectedYear ?? 2026 ?>">
                <div class="modal-header bg-body border-bottom py-3">
                    <h5 class="modal-title fw-bold">Tambah Pos Anggaran RKA (<?= $selectedYear ?? 2026 ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Pos Anggaran / Kegiatan</label>
                        <input type="text" class="form-control" name="title" placeholder="contoh: Pengadaan Lisensi Perangkat Lunak Laboratorium" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Anggaran</label>
                            <select name="category" class="form-select" required>
                                <option value="RKA Operasional">RKA Operasional</option>
                                <option value="RKA Pengembangan">RKA Pengembangan</option>
                                <option value="Penelitian">Penelitian Dosen</option>
                                <option value="PkM">Pengabdian Masyarakat (PkM)</option>
                                <option value="Kemahasiswaan">Kegiatan Kemahasiswaan & Prestasi</option>
                                <option value="Pendapatan">Pendapatan / Kerja Sama</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Unit / Program Studi</label>
                            <select name="study_program_id" class="form-select">
                                <option value="0">Dekanat Fakultas</option>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pagu Anggaran Disetujui (Rp)</label>
                            <input type="number" step="1000" min="0" class="form-control" name="budgeted_amount" placeholder="contoh: 50000000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Realisasi Awal Belanja (Rp)</label>
                            <input type="number" step="1000" min="0" class="form-control" name="realized_amount" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Pos Anggaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
