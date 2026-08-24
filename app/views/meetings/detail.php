<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i></a>
            <span class="badge bg-primary fs-6"><?= $meeting['meeting_number'] ?></span>
            <?= FormatHelper::statusBadge($meeting['status']) ?>
        </div>
        <h3 class="fw-bold text-dark mb-0"><?= htmlspecialchars($meeting['title'], ENT_QUOTES, 'UTF-8') ?></h3>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUploadDoc">
            <i class="ti ti-upload me-1"></i> Unggah Dokumen Rapat
        </button>
    </div>
</div>

<!-- Meeting Info Card -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <span class="text-muted small">Jenis Rapat</span>
                <div class="fw-bold text-dark"><?= $meeting['type'] ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Tanggal & Waktu</span>
                <div class="fw-bold text-dark"><?= FormatHelper::indonesianDate($meeting['meeting_date']) ?> • <?= substr($meeting['start_time'], 0, 5) ?> WIB</div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Lokasi Rapat</span>
                <div class="fw-bold text-dark"><?= htmlspecialchars($meeting['location'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Pimpinan Rapat</span>
                <div class="fw-bold text-dark"><?= htmlspecialchars($meeting['chairperson_name'] ?? 'Dekan FTIK', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <hr class="my-3">
        <div>
            <span class="text-muted small fw-semibold text-uppercase">Agenda & Risalah Pembahasan:</span>
            <p class="small text-dark mb-0 mt-1" style="white-space: pre-line;"><?= htmlspecialchars($meeting['agenda'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</div>

<!-- Digital Packet Tabs (Undangan, Absensi, Notulensi, Materi, Foto, RTL) -->
<div class="row g-4 mb-4">
    <!-- 1. Digital Documents Packet -->
    <div class="col-12 col-xl-6">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="ti ti-folder text-primary fs-4"></i> Arsip Digital Rapat (1 Paket)
                </h5>
                <span class="badge bg-primary-subtle text-primary"><?= count($meeting['documents']) ?> Dokumen</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($meeting['documents'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-files text-muted fs-1 mb-2 d-block"></i>
                        Belum ada dokumen yang diunggah untuk rapat ini.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($meeting['documents'] as $doc): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-file-text fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-light text-dark border mb-1"><?= $doc['document_type'] ?></span>
                                        <h6 class="mb-0 fw-semibold text-dark"><?= htmlspecialchars($doc['file_title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                        <small class="text-muted"><?= round($doc['file_size'] / 1024, 1) ?> KB • Oleh: <?= htmlspecialchars($doc['uploaded_by'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                                <a href="<?= $baseUrl ?>/<?= $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download"></i> Unduh
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2. Action Items (RTL Rapat) -->
    <div class="col-12 col-xl-6">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="ti ti-arrow-guide text-info fs-4"></i> Tindak Lanjut Hasil Rapat (RTL)
                </h5>
                <a href="<?= $baseUrl ?>/meetings/rtl" class="btn btn-sm btn-outline-info">Buka Tracking RTL</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($meeting['action_items'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-checklist text-muted fs-1 mb-2 d-block"></i>
                        Tidak ada butir tindak lanjut (RTL) khusus untuk rapat ini.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($meeting['action_items'] as $rtl): ?>
                            <div class="list-group-item p-3 px-4">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge bg-secondary"><?= $rtl['item_code'] ?></span>
                                    <?= FormatHelper::statusBadge($rtl['status']) ?>
                                </div>
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($rtl['description'], ENT_QUOTES, 'UTF-8') ?></h6>
                                <div class="d-flex align-items-center justify-content-between small text-muted">
                                    <span>PIC: <strong><?= htmlspecialchars($rtl['pic_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                    <span>Deadline: <strong><?= FormatHelper::indonesianDate($rtl['deadline']) ?></strong></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Dokumen -->
<div class="modal fade" id="modalUploadDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/meetings/upload-document" method="POST" enctype="multipart/form-data">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="meeting_id" value="<?= $meeting['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Unggah Dokumen Paket Digital Rapat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipe Dokumen</label>
                        <select name="document_type" class="form-select" required>
                            <option value="Undangan">Surat Undangan</option>
                            <option value="Daftar Hadir">Daftar Hadir / Absensi</option>
                            <option value="Materi">Bahan Tayang / Materi</option>
                            <option value="Notulensi" selected>Risalah Notulensi Resmi</option>
                            <option value="Foto">Dokumentasi / Foto Kegiatan</option>
                            <option value="Lainnya">Dokumen Pendukung Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul / Keterangan Berkas</label>
                        <input type="text" class="form-control" name="file_title" placeholder="contoh: Notulensi Rapim 15 Agustus 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File (PDF, DOCX, XLSX, JPG, PNG)</label>
                        <input type="file" class="form-control" name="document_file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
