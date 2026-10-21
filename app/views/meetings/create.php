<?php use App\Helpers\CsrfHelper; ?>
<div class="card border-0 shadow-sm rounded-4 w-100 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-1 text-body d-flex align-items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-primary"></i> Buat Jadwal Rapat & Agenda Dekanat Baru
            </h5>
            <p class="text-muted small mb-0">Isi formulir lengkap untuk membuat paket digital agenda rapat, undangan, dan notulensi dekanat.</p>
        </div>
        <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary rounded-3 px-3 d-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Rapat
        </a>
    </div>
    <div class="card-body p-4 p-lg-5">
        <form action="<?= $baseUrl ?>/meetings/create" method="POST">
            <?= CsrfHelper::tokenField() ?>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label small fw-semibold text-muted">Judul Rapat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 py-2" name="title" placeholder="contoh: Rapat Koordinasi Pimpinan Fakultas & Evaluasi Kinerja Semester..." required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Jenis Rapat <span class="text-danger">*</span></label>
                    <select name="type" class="form-select rounded-3 py-2">
                        <option value="Rapat Pimpinan" selected>Rapat Pimpinan (Dekanat)</option>
                        <option value="Rapat Kaprodi">Rapat Koordinasi Kaprodi</option>
                        <option value="Rapat Dosen Prodi">Rapat Dosen Program Studi</option>
                        <option value="Rapat Gabungan">Rapat Gabungan</option>
                        <option value="Rapat Senat">Rapat Pleno Senat Fakultas</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Lokasi / Ruang Rapat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 py-2" name="location" placeholder="Ruang Rapat Dekanat Lt. 3 / Zoom Meeting" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control rounded-3 py-2" name="meeting_date" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control rounded-3 py-2" name="start_time" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Pimpinan Rapat <span class="text-danger">*</span></label>
                    <select name="chairperson_id" class="form-select rounded-3 py-2">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u['role_slug'] === 'dekan' ? 'selected' : '' ?>><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Notulis / Sekretaris Rapat <span class="text-danger">*</span></label>
                    <select name="secretary_id" class="form-select rounded-3 py-2">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-muted">Agenda & Pembahasan Utama <span class="text-danger">*</span></label>
                    <textarea name="agenda" class="form-control rounded-3 p-3" rows="5" placeholder="1. Pembahasan serapan anggaran...&#10;2. Evaluasi BKD dosen...&#10;3. Kesiapan akreditasi LAM-INFOKOM..." required></textarea>
                    <div class="form-text small text-muted">Tuliskan butir-butir agenda utama yang akan dibahas pada rapat ini.</div>
                </div>

                <div class="col-12 pt-2">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <a href="<?= $baseUrl ?>/meetings" class="btn btn-outline-secondary rounded-3 px-4 py-2">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-calendar-plus"></i> Buat & Buka Paket Digital
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

