<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1"><i class="fa-solid fa-arrow-left"></i></a>
            <span class="badge badge-soft-primary font-monospace fs-6"><?= htmlspecialchars($meeting['meeting_number'], ENT_QUOTES, 'UTF-8') ?></span>
            <?= FormatHelper::statusBadge($meeting['status']) ?>
        </div>
        <h4 class="fw-bold text-body mb-0"><?= htmlspecialchars($meeting['title'], ENT_QUOTES, 'UTF-8') ?></h4>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalUploadDoc">
            <i class="fa-solid fa-cloud-arrow-up"></i> Unggah Dokumen Rapat
        </button>
    </div>
</div>

<!-- Meeting Info Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <span class="text-muted small fw-semibold">Jenis Rapat</span>
                <div class="fw-bold text-body"><?= htmlspecialchars($meeting['type'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-semibold">Tanggal & Waktu</span>
                <div class="fw-bold text-body"><?= FormatHelper::indonesianDate($meeting['meeting_date']) ?> &bull; <?= substr($meeting['start_time'], 0, 5) ?> WIB</div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-semibold">Lokasi Rapat</span>
                <div class="fw-bold text-body"><i class="fa-solid fa-location-dot text-primary me-1"></i><?= htmlspecialchars($meeting['location'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small fw-semibold">Pimpinan Rapat</span>
                <div class="fw-bold text-body"><i class="fa-solid fa-user-tie text-primary me-1"></i><?= htmlspecialchars($meeting['chairperson_name'] ?? 'Dekan FTIK', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <hr class="my-3 border-light-subtle">
        <div>
            <span class="text-muted small fw-semibold text-uppercase d-flex align-items-center gap-1 mb-1">
                <i class="fa-solid fa-clipboard-list text-primary"></i> Agenda & Risalah Pembahasan:
            </span>
            <div class="small text-body bg-body-tertiary p-3 rounded-3" style="white-space: pre-line;"><?= htmlspecialchars($meeting['agenda'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</div>

<!-- Digital Packet Tabs (Undangan, Absensi, Notulensi, Materi, Foto, RTL) -->
<div class="row g-4 mb-4">
    <!-- 1. Digital Documents Packet -->
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-closed text-primary"></i> Arsip Digital Rapat (1 Paket)
                </h5>
                <span class="badge badge-soft-primary"><?= count($meeting['documents']) ?> Dokumen</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($meeting['documents'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open text-muted fs-1 mb-2 d-block opacity-50"></i>
                        Belum ada dokumen yang diunggah untuk rapat ini.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($meeting['documents'] as $doc): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 bg-transparent border-light-subtle">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center p-2">
                                        <i class="fa-solid fa-file-pdf fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="badge badge-soft-secondary mb-1"><?= htmlspecialchars($doc['document_type'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <h6 class="mb-0 fw-semibold text-body"><?= htmlspecialchars($doc['file_title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                        <small class="text-muted"><?= round($doc['file_size'] / 1024, 1) ?> KB &bull; Oleh: <?= htmlspecialchars($doc['uploaded_by'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                                <a href="<?= $baseUrl ?>/<?= $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-soft-primary rounded-3 px-3 py-1 d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-download"></i> <span>Unduh</span>
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
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-info"></i> Tindak Lanjut Hasil Rapat (RTL)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($meeting['action_items'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clipboard-check text-muted fs-1 mb-2 d-block opacity-50"></i>
                        Tidak ada butir tindak lanjut (RTL) khusus untuk rapat ini.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($meeting['action_items'] as $rtl): ?>
                            <div class="list-group-item p-3 px-4 bg-transparent border-light-subtle">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge badge-soft-secondary font-monospace"><?= htmlspecialchars($rtl['item_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?= FormatHelper::statusBadge($rtl['status']) ?>
                                </div>
                                <h6 class="fw-bold text-body mb-1"><?= htmlspecialchars($rtl['description'], ENT_QUOTES, 'UTF-8') ?></h6>
                                <div class="d-flex align-items-center justify-content-between small text-muted">
                                    <span>PIC: <strong class="text-body"><?= htmlspecialchars($rtl['pic_name'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                    <span>Deadline: <strong class="text-body"><?= FormatHelper::indonesianDate($rtl['deadline']) ?></strong></span>
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
        <div class="modal-content border-0 shadow-lg rounded-4 text-start">
            <form action="<?= $baseUrl ?>/meetings/upload-document" method="POST" enctype="multipart/form-data">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="meeting_id" value="<?= $meeting['id'] ?>">
                <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up text-primary"></i> Unggah Dokumen Paket Digital
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Tipe Dokumen</label>
                        <select name="document_type" class="form-select rounded-3" required>
                            <option value="Undangan">Surat Undangan</option>
                            <option value="Daftar Hadir">Daftar Hadir / Absensi</option>
                            <option value="Materi">Bahan Tayang / Materi</option>
                            <option value="Notulensi" selected>Risalah Notulensi Resmi</option>
                            <option value="Foto">Dokumentasi / Foto Kegiatan</option>
                            <option value="Lainnya">Dokumen Pendukung Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Judul / Keterangan Berkas</label>
                        <input type="text" class="form-control rounded-3" name="file_title" placeholder="contoh: Notulensi Rapim 15 Agustus 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Pilih File (PDF, DOCX, XLSX, JPG, PNG)</label>
                        <input type="file" class="form-control rounded-3" name="document_file" required>
                    </div>
                </div>
                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Unggah Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

