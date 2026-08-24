<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-checklist text-warning fs-2"></i> <?= $canApprove ? 'Pengelolaan & Persetujuan Dekan (Approvals)' : 'Status Pengajuan & Persetujuan Dekan' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= $canApprove ? 'Tinjau, setujui, atau tolak permohonan kegiatan, riset, anggaran, dan surat resmi fakultas.' : 'Pantau status permohonan kegiatan, riset, dan anggaran yang Anda ajukan ke Dekan.' ?>
        </p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary shadow-sm btn-crud" data-bs-toggle="modal" data-bs-target="#modalSubmitApproval">
            <i class="ti ti-plus"></i> Buat Pengajuan Baru
        </button>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Pengajuan Masuk & Status</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($approvals) ?> Pengajuan</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($approvals)): ?>
            <div class="text-center py-5 text-muted">
                <i class="ti ti-checklist text-muted fs-1 mb-2 d-block"></i>
                Tidak ada data pengajuan saat ini.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Modul</th>
                            <th>Judul Pengajuan</th>
                            <th>Pemohon & Prodi</th>
                            <th>Tgl Pengajuan</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi / Catatan Dekan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvals as $app): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border"><?= $app['module'] ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($app['notes'])): ?>
                                        <small class="text-muted"><i class="ti ti-notes me-1"></i>Catatan: <?= htmlspecialchars($app['notes'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($app['requester_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['program_name'] ?? 'Fakultas', ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td><?= FormatHelper::indonesianDate($app['submission_date']) ?></td>
                                <td><?= FormatHelper::statusBadge($app['status']) ?></td>
                                <td class="text-end pe-4 text-nowrap">
                                    <?php if ($app['status'] === 'Pending'): ?>
                                        <?php if ($canApprove): ?>
                                            <div class="table-actions">
                                                <button type="button" class="btn btn-sm btn-success btn-crud-sm" data-bs-toggle="modal" data-bs-target="#approvalModal<?= $app['id'] ?>" title="Beri Keputusan">
                                                    <i class="ti ti-check"></i> Keputusan Dekan
                                                </button>
                                            </div>

                                            <!-- Modal Approval for Dekan -->
                                            <div class="modal fade" id="approvalModal<?= $app['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow text-start">
                                                        <form action="<?= $baseUrl ?>/command-center/approve" method="POST">
                                                            <?= CsrfHelper::tokenField() ?>
                                                            <input type="hidden" name="approval_id" value="<?= $app['id'] ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Keputusan Dekan: <?= htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Pilih Tindakan</label>
                                                                    <select name="action" class="form-select" required>
                                                                        <option value="Approved" selected>✅ Setujui Pengajuan</option>
                                                                        <option value="Rejected">❌ Tolak Pengajuan</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Catatan / Arahan Dekan</label>
                                                                    <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan arahan atau disposisi tindak lanjut..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1.5 px-2.5">
                                                <i class="ti ti-clock me-1"></i> Menunggu Persetujuan Dekan
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="small">
                                            <span class="fw-semibold text-dark">Diputuskan oleh: <?= htmlspecialchars($app['approved_by'] ?? 'Dekan', ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if (!empty($app['approved_at'])): ?>
                                                <div class="text-muted" style="font-size: 0.75rem;"><?= FormatHelper::indonesianDate($app['approved_at'], true) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Buat Pengajuan Baru (Untuk Kaprodi / Dosen / Dekan) -->
<div class="modal fade" id="modalSubmitApproval" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/command-center/approvals/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Buat Pengajuan Baru ke Dekan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori / Modul Pengajuan</label>
                        <select name="module" class="form-select" required>
                            <option value="Kegiatan" selected>Proposal Kegiatan / Seminar / Kuliah Tamu</option>
                            <option value="Penelitian">Usulan Hibah Penelitian & PkM</option>
                            <option value="Kerjasama">Naskah Kerja Sama (MoU / MoA / IA)</option>
                            <option value="Anggaran">Pengajuan Anggaran Operasional RKA</option>
                            <option value="Surat">Permohonan Surat Tugas / SK Dekan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Permohonan / Kegiatan</label>
                        <input type="text" class="form-control" name="title" placeholder="contoh: Pengajuan Pelaksanaan Kuliah Tamu AI 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Uraian / Ringkasan Kebutuhan</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Jelaskan latar belakang, estimasi anggaran, atau link proposal pendukung..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
