<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Kerja Sama Aktif</span>
            <h3 class="fw-bold mb-0 mt-1 text-success"><?= $totalActive ?> <span class="fs-6 fw-normal text-muted">Kemitraan</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-warning">Akan Segera Berakhir</span>
            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $totalExpiring ?> <span class="fs-6 fw-normal text-muted">Dokumen</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Realisasi Aktivitas Nyata</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $totalActivities ?> <span class="fs-6 fw-normal text-muted">Kegiatan</span></h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Dokumen Kemitraan & Kerja Sama (MoU / MoA / IA)</h5>
        <button type="button" class="btn btn-sm btn-primary btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddCooperation">
            <i class="ti ti-plus me-1"></i> Tambah Kerja Sama
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Mitra & Dokumen</th>
                        <th>Tingkat</th>
                        <th>Ruang Lingkup</th>
                        <th>Periode Berlaku</th>
                        <th>Sisa Waktu</th>
                        <th>Realisasi Kegiatan</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Kemitraan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cooperations as $c): ?>
                        <tr class="<?= $c['status'] === 'Akan Berakhir' ? 'table-warning' : ($c['status'] === 'Kadaluarsa' ? 'table-danger' : '') ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['partner_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <span class="badge bg-light text-dark border"><?= $c['type'] ?></span>
                                <small class="text-muted ms-1">PIC: <?= htmlspecialchars($c['pic_internal'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle"><?= $c['level'] ?></span></td>
                            <td style="max-width: 240px;"><small class="text-muted"><?= htmlspecialchars($c['scope'], ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td><?= FormatHelper::indonesianDate($c['start_date']) ?> s/d <?= FormatHelper::indonesianDate($c['end_date']) ?></td>
                            <td>
                                <?php if ($c['days_remaining'] <= 30 && $c['days_remaining'] >= 0): ?>
                                    <span class="badge bg-danger-subtle text-danger fw-bold"><?= $c['days_remaining'] ?> Hari Lagi</span>
                                <?php elseif ($c['days_remaining'] < 0): ?>
                                    <span class="badge bg-danger text-white">Kadaluarsa</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success"><?= $c['days_remaining'] ?> Hari</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-primary"><?= $c['real_activities_count'] ?></strong> Kegiatan
                            </td>
                            <td><?= FormatHelper::statusBadge($c['status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditCooperation<?= $c['id'] ?>" title="Update Kemitraan">
                                        <i class="ti ti-edit"></i> Aksi Mitra
                                    </button>
                                    <form action="<?= $baseUrl ?>/cooperations/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kemitraan dengan <?= htmlspecialchars($c['partner_name'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-square" title="Hapus Kerja Sama">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit & Aksi Kemitraan -->
                                <div class="modal fade" id="modalEditCooperation<?= $c['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-handshake text-primary"></i> Update & Aksi Kemitraan Strategis
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/cooperations/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Nama Mitra Industri / Kampus</label>
                                                        <input type="text" class="form-control" name="partner_name" value="<?= htmlspecialchars($c['partner_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Tipe Dokumen</label>
                                                            <select name="type" class="form-select">
                                                                <option value="MoU" <?= $c['type'] === 'MoU' ? 'selected' : '' ?>>MoU (Nota Kesepahaman)</option>
                                                                <option value="MoA" <?= $c['type'] === 'MoA' ? 'selected' : '' ?>>MoA (Perjanjian Kerja Sama)</option>
                                                                <option value="IA" <?= $c['type'] === 'IA' ? 'selected' : '' ?>>IA (Implementation Arrangement)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Tingkat Kerja Sama</label>
                                                            <select name="level" class="form-select">
                                                                <option value="Internasional" <?= $c['level'] === 'Internasional' ? 'selected' : '' ?>>Internasional</option>
                                                                <option value="Nasional" <?= $c['level'] === 'Nasional' ? 'selected' : '' ?>>Nasional</option>
                                                                <option value="Lokal" <?= $c['level'] === 'Lokal' ? 'selected' : '' ?>>Lokal / Wilayah</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Status Kemitraan</label>
                                                            <select name="status" class="form-select">
                                                                <option value="Aktif" <?= $c['status'] === 'Aktif' ? 'selected' : '' ?>>🟢 Aktif</option>
                                                                <option value="Akan Berakhir" <?= $c['status'] === 'Akan Berakhir' ? 'selected' : '' ?>>🟡 Akan Berakhir (< 30 Hari)</option>
                                                                <option value="Kadaluarsa" <?= $c['status'] === 'Kadaluarsa' ? 'selected' : '' ?>>🔴 Kadaluarsa</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Ruang Lingkup</label>
                                                        <input type="text" class="form-control" name="scope" value="<?= htmlspecialchars($c['scope'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                                                            <input type="date" class="form-control" name="start_date" value="<?= $c['start_date'] ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Tanggal Berakhir</label>
                                                            <input type="date" class="form-control" name="end_date" value="<?= $c['end_date'] ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Realisasi Kegiatan Nyata</label>
                                                            <input type="number" min="0" class="form-control" name="real_activities_count" value="<?= $c['real_activities_count'] ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">PIC Fakultas</label>
                                                        <input type="text" class="form-control" name="pic_internal" value="<?= htmlspecialchars($c['pic_internal'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                        <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan Mitra
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

<!-- Modal Tambah Kerja Sama -->
<div class="modal fade" id="modalAddCooperation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= $baseUrl ?>/cooperations/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header bg-body border-bottom py-3">
                    <h5 class="modal-title fw-bold">Tambah Dokumen Kerja Sama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Mitra Industri / Kampus</label>
                        <input type="text" class="form-control" name="partner_name" placeholder="contoh: PT Telkom Indonesia (Persero) Tbk" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipe Dokumen</label>
                            <select name="type" class="form-select">
                                <option value="MoU">MoU (Nota Kesepahaman)</option>
                                <option value="MoA" selected>MoA (Perjanjian Kerja Sama)</option>
                                <option value="IA">IA (Implementation Arrangement)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tingkat Kerja Sama</label>
                            <select name="level" class="form-select">
                                <option value="Internasional">Internasional</option>
                                <option value="Nasional" selected>Nasional</option>
                                <option value="Lokal">Lokal / Wilayah</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ruang Lingkup</label>
                        <input type="text" class="form-control" name="scope" placeholder="Pendidikan, Riset, MBKM, dll." required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Berakhir</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Realisasi Kegiatan Nyata</label>
                            <input type="number" min="0" class="form-control" name="real_activities_count" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">PIC Fakultas</label>
                        <input type="text" class="form-control" name="pic_internal" placeholder="Nama dosen / pimpinan" required>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Kerja Sama</button>
                </div>
            </form>
        </div>
    </div>
</div>
