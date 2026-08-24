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
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Daftar Dokumen Kemitraan & Kerja Sama (MoU / MoA / IA)</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCooperation">
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
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cooperations as $c): ?>
                        <tr class="<?= $c['status'] === 'Akan Berakhir' ? 'table-warning' : '' ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['partner_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <span class="badge bg-light text-dark border"><?= $c['type'] ?></span>
                                <small class="text-muted ms-1">PIC: <?= htmlspecialchars($c['pic_internal'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle"><?= $c['level'] ?></span></td>
                            <td style="max-width: 250px;"><small class="text-muted"><?= htmlspecialchars($c['scope'], ENT_QUOTES, 'UTF-8') ?></small></td>
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
                            <td><strong><?= $c['real_activities_count'] ?></strong> Aktivitas</td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($c['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kerja Sama -->
<div class="modal fade" id="modalAddCooperation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/cooperations/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Dokumen Kerja Sama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Mitra Industri / Kampus</label>
                        <input type="text" class="form-control" name="partner_name" placeholder="contoh: PT Telkom Indonesia" required>
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
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Berakhir</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">PIC Fakultas</label>
                        <input type="text" class="form-control" name="pic_internal" placeholder="Nama dosen / pimpinan" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kerja Sama</button>
                </div>
            </form>
        </div>
    </div>
</div>
