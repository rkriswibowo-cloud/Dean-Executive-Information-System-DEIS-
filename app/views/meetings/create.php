<?php use App\Helpers\CsrfHelper; ?>
<div class="row g-4 justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-calendar-plus text-primary"></i> Buat Jadwal Rapat & Agenda Dekanat Baru
                </h5>
                <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary rounded-3 px-3 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="<?= $baseUrl ?>/meetings/create" method="POST">
                    <?= CsrfHelper::tokenField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Judul Rapat</label>
                        <input type="text" class="form-control rounded-3" name="title" placeholder="contoh: Rapat Koordinasi Pimpinan Fakultas..." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Jenis Rapat</label>
                            <select name="type" class="form-select rounded-3">
                                <option value="Rapat Pimpinan" selected>Rapat Pimpinan (Dekanat)</option>
                                <option value="Rapat Kaprodi">Rapat Koordinasi Kaprodi</option>
                                <option value="Rapat Dosen Prodi">Rapat Dosen Program Studi</option>
                                <option value="Rapat Gabungan">Rapat Gabungan</option>
                                <option value="Rapat Senat">Rapat Pleno Senat Fakultas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Lokasi / Ruang Rapat</label>
                            <input type="text" class="form-control rounded-3" name="location" placeholder="Ruang Rapat Dekanat Lt. 3" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Tanggal Pelaksanaan</label>
                            <input type="date" class="form-control rounded-3" name="meeting_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Waktu Mulai</label>
                            <input type="time" class="form-control rounded-3" name="start_time" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Pimpinan Rapat</label>
                            <select name="chairperson_id" class="form-select rounded-3">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $u['role_slug'] === 'dekan' ? 'selected' : '' ?>><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Notulis / Sekretaris Rapat</label>
                            <select name="secretary_id" class="form-select rounded-3">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Agenda & Pembahasan Utama</label>
                        <textarea name="agenda" class="form-control rounded-3" rows="4" placeholder="1. Pembahasan serapan anggaran...&#10;2. Evaluasi BKD dosen...&#10;3. Kesiapan akreditasi LAM-INFOKOM..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $baseUrl ?>/meetings" class="btn btn-outline-secondary rounded-3 px-3">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm fw-semibold">
                            <i class="fa-solid fa-calendar-plus me-1"></i> Buat & Buka Paket Digital
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

