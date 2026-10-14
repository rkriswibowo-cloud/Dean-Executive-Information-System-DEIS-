<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Pagu Anggaran RKA (<?= $selectedYear ?? 2026 ?>)</div>
                <div class="kpi-value text-body"><?= FormatHelper::currency($totalBudget) ?></div>
                <div class="small text-muted mt-1"><i class="fa-regular fa-calendar-check me-1 text-primary"></i> Alokasi pagu tahun berjalan</div>
            </div>
            <div class="kpi-icon-wrap primary">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Total Realisasi Belanja</div>
                <div class="kpi-value text-primary"><?= FormatHelper::currency($totalRealized) ?></div>
                <div class="small text-muted mt-1"><i class="fa-solid fa-receipt me-1 text-info"></i> Akumulasi pencairan dana</div>
            </div>
            <div class="kpi-icon-wrap info">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card shadow-sm">
            <div>
                <div class="kpi-label">Tingkat Serapan Anggaran</div>
                <div class="kpi-value <?= $overallAbsorption >= 85 ? 'text-success' : ($overallAbsorption >= 60 ? 'text-warning' : 'text-danger') ?>"><?= $overallAbsorption ?>%</div>
                <div class="small text-muted mt-1">
                    <span class="badge <?= $overallAbsorption >= 85 ? 'badge-soft-success' : ($overallAbsorption >= 60 ? 'badge-soft-warning' : 'badge-soft-danger') ?>">
                        <i class="fa-solid <?= $overallAbsorption >= 85 ? 'fa-circle-check' : ($overallAbsorption >= 60 ? 'fa-triangle-exclamation' : 'fa-circle-exclamation') ?> me-1"></i>
                        <?= $overallAbsorption >= 85 ? 'Target Optimal' : ($overallAbsorption >= 60 ? 'Perlu Akselerasi' : 'Kritis / Rendah') ?>
                    </span>
                </div>
            </div>
            <div class="kpi-icon-wrap <?= $overallAbsorption >= 85 ? 'success' : ($overallAbsorption >= 60 ? 'warning' : 'danger') ?>">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-primary"></i> Rincian Anggaran RKA & Evaluasi Serapan Fakultas
            </h5>
            <span class="text-muted small">Tahun Anggaran <?= $selectedYear ?? 2026 ?> &bull; Rekapitulasi per Program Studi & Unit</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddFinance">
                <i class="fa-solid fa-plus"></i> Tambah Pos Anggaran
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
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
                                <span class="badge badge-soft-secondary mb-1"><?= htmlspecialchars($f['category'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="fw-bold text-body"><?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <span class="text-body fw-medium"><?= htmlspecialchars($f['program_name'] ?? 'Dekanat Fakultas', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td><strong class="text-body"><?= FormatHelper::currency($f['budgeted_amount']) ?></strong></td>
                            <td><strong class="text-primary"><?= FormatHelper::currency($f['realized_amount']) ?></strong></td>
                            <td style="min-width: 140px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-body"><?= $f['absorption_percentage'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div class="progress-bar bg-<?= $f['absorption_percentage'] >= 85 ? 'success' : ($f['absorption_percentage'] >= 60 ? 'warning' : 'danger') ?>" style="width: <?= min(100, $f['absorption_percentage']) ?>%"></div>
                                </div>
                            </td>
                            <td><?= FormatHelper::statusBadge($f['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-soft-primary rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalEditFinance<?= $f['id'] ?>" title="Update Realisasi & Evaluasi">
                                        <i class="fa-solid fa-pen-to-square"></i> <span>Realisasi</span>
                                    </button>
                                    <form action="<?= $baseUrl ?>/finances/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pos anggaran <?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-soft-danger rounded-3 px-2 py-1" title="Hapus Pos Anggaran">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit & Aksi Realisasi Anggaran -->
                                <div class="modal fade" id="modalEditFinance<?= $f['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                                                <h6 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i> Evaluasi & Update Realisasi Anggaran
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
                                                        <input type="text" class="form-control rounded-3" name="title" value="<?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Kategori Anggaran</label>
                                                            <select name="category" class="form-select rounded-3" required>
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
                                                            <select name="study_program_id" class="form-select rounded-3">
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
                                                            <input type="number" step="1000" min="0" class="form-control rounded-3" name="budgeted_amount" value="<?= $f['budgeted_amount'] ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold text-muted">Total Realisasi Belanja (Rp)</label>
                                                            <input type="number" step="1000" min="0" class="form-control rounded-3" name="realized_amount" value="<?= $f['realized_amount'] ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Evaluasi Anggaran
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
                <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-plus text-primary"></i> Tambah Pos Anggaran RKA (<?= $selectedYear ?? 2026 ?>)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Pos Anggaran / Kegiatan</label>
                        <input type="text" class="form-control rounded-3" name="title" placeholder="contoh: Pengadaan Lisensi Perangkat Lunak Laboratorium" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kategori Anggaran</label>
                            <select name="category" class="form-select rounded-3" required>
                                <option value="RKA Operasional">RKA Operasional</option>
                                <option value="RKA Pengembangan">RKA Pengembangan</option>
                                <option value="Penelitian">Penelitian Dosen</option>
                                <option value="PkM">Pengabdian Masyarakat (PkM)</option>
                                <option value="Kemahasiswaan">Kegiatan Kemahasiswaan & Prestasi</option>
                                <option value="Pendapatan">Pendapatan / Kerja Sama</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Unit / Program Studi</label>
                            <select name="study_program_id" class="form-select rounded-3">
                                <option value="0">Dekanat Fakultas</option>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Pagu Anggaran Disetujui (Rp)</label>
                            <input type="number" step="1000" min="0" class="form-control rounded-3" name="budgeted_amount" placeholder="contoh: 50000000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Realisasi Awal Belanja (Rp)</label>
                            <input type="number" step="1000" min="0" class="form-control rounded-3" name="realized_amount" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pos Anggaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

