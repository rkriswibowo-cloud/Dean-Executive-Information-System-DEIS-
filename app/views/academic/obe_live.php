<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header Realtime OBE -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white banner-executive-card" style="background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <a href="<?= $baseUrl ?>/academic/rps-monitoring" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 mb-3 px-3 shadow-none">
                        <i class="ti ti-arrow-left me-1"></i> Kembali ke Monitoring RPS
                    </a>
                    <div class="badge bg-white text-success fw-bold mb-2 px-3 py-1 shadow-sm">
                        <i class="ti ti-database me-1"></i> Live Cross-Database Query: obe_cpl
                    </div>
                    <h3 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">Data Realtime Mata Kuliah & RPS (Sistem OBE)</h3>
                    <div class="small" style="color: rgba(255, 255, 255, 0.9) !important;">
                        Melihat langsung 147+ mata kuliah aktif, kurikulum OBE, serta pemetaan silabus RPS yang tersimpan di basis data sistem OBE secara realtime tanpa jeda.
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form action="<?= $baseUrl ?>/academic/obe-snapshot" method="POST" onsubmit="return confirm('Simpan kondisi master OBE saat ini sebagai snapshot historis di DEIS?');">
                        <?= CsrfHelper::tokenField() ?>
                        <button type="submit" class="btn btn-light fw-bold text-success shadow-sm">
                            <i class="ti ti-camera me-1"></i> Simpan Snapshot Historis
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($snapshots)): ?>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent py-3 px-4"><h6 class="fw-bold mb-0"><i class="ti ti-history me-1"></i> Snapshot OBE Terakhir</h6></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="bg-body-tertiary"><tr><th class="ps-4">Waktu</th><th>Label</th><th>Record</th><th>Checksum</th></tr></thead>
        <tbody><?php foreach ($snapshots as $snapshot): ?><tr>
            <td class="ps-4"><?= htmlspecialchars($snapshot['created_at']) ?></td>
            <td><?= htmlspecialchars($snapshot['label']) ?></td>
            <td><?= number_format((int)$snapshot['record_count']) ?></td>
            <td><code><?= htmlspecialchars(substr($snapshot['checksum'], 0, 12)) ?>…</code></td>
        </tr><?php endforeach; ?></tbody>
    </table></div></div>
</div>
<?php endif; ?>

<!-- Tabel Realtime Mata Kuliah OBE -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="card-title fw-bold mb-0">Katalog Mata Kuliah Langsung dari OBE</h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><?= count($obeCourses) ?> Mata Kuliah Aktif</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/academic/obe-live?prodi_id=' + this.value">
                <option value="">Semua Program Studi OBE</option>
                <option value="1" <?= $filterProdi == 1 ? 'selected' : '' ?>>Sistem Informasi</option>
                <option value="3" <?= $filterProdi == 3 ? 'selected' : '' ?>>Rekayasa Perangkat Lunak</option>
                <option value="4" <?= $filterProdi == 4 ? 'selected' : '' ?>>Bisnis Digital</option>
                <option value="5" <?= $filterProdi == 5 ? 'selected' : '' ?>>Kewirausahaan</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode & Mata Kuliah</th>
                        <th>Program Studi & Kurikulum</th>
                        <th>SKS</th>
                        <th>Semester</th>
                        <th>Kelompok Keilmuan</th>
                        <th>PJMK / Pengampu OBE</th>
                        <th>Status Dokumen RPS</th>
                        <th>IKU-7 (Case Method)</th>
                        <th class="text-end pe-4">Aksi Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($obeCourses)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                Tidak ada data mata kuliah yang ditemukan di sistem OBE.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($obeCourses as $m): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($m['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($m['sifat'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($m['prodi_nama'], ENT_QUOTES, 'UTF-8') ?> (<?= $m['prodi_jenjang'] ?>)</div>
                                    <div class="small text-muted"><?= htmlspecialchars($m['kurikulum_nama'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= $m['sks'] ?> SKS</td>
                                <td>Semester <?= $m['semester'] ?></td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($m['kelompok_keilmuan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="min-width: 190px;">
                                    <?php if (!empty($m['pjmk_nama'])): ?>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($m['pjmk_nama'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="small text-muted">
                                            NIDN: <?= htmlspecialchars($m['pjmk_nidn'] ?: '-', ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($m['pjmk_tahun_akademik'])): ?>
                                                &middot; <?= htmlspecialchars($m['pjmk_tahun_akademik'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($m['deis_lecturer_id'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                <i class="ti ti-link me-1"></i>Cocok di DEIS
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle mt-1">
                                                <i class="ti ti-unlink me-1"></i>Belum cocok di DEIS
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif (!empty($m['rps_dosen_pengampu'])): ?>
                                        <div class="small fw-semibold text-dark" style="white-space: pre-line;"><?= htmlspecialchars($m['rps_dosen_pengampu'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle mt-1">
                                            <i class="ti ti-file-text me-1"></i>Dari dokumen RPS
                                        </span>
                                        <div class="small text-muted mt-1">Belum ada penugasan PJMK aktif yang dapat dicocokkan via NIDN.</div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['rps_status'] === 'Lengkap'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ti ti-check me-1"></i>Lengkap (RPS Ada)</span>
                                    <?php elseif ($m['rps_status'] === 'Revisi'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="ti ti-alert-triangle me-1"></i>Draft / Revisi</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-x me-1"></i>Belum Ada RPS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['is_case_method'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="ti ti-check me-1"></i>Case / Project
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Reguler</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <?php if (!empty($m['obe_rps_id'])): ?>
                                        <a href="http://localhost/obe/rps/preview/<?= $m['obe_mk_id'] ?>" target="_blank" class="btn btn-sm btn-outline-success btn-crud-sm">
                                            <i class="ti ti-external-link me-1"></i> Buka di OBE
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
