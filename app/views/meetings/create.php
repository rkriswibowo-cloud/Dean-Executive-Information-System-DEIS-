<?php use App\Helpers\CsrfHelper; ?>
<div class="row g-4 justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card card-lg shadow-sm border-0 rounded-4">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0">Buat Jadwal Rapat & Agenda Dekanat Baru</h5>
                <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">← Kembali</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= $baseUrl ?>/meetings/create" method="POST">
                    <?= CsrfHelper::tokenField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Rapat</label>
                        <input type="text" class="form-control" name="title" placeholder="contoh: Rapat Koordinasi Pimpinan Fakultas..." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Rapat</label>
                            <select name="type" class="form-select">
                                <option value="Rapat Pimpinan" selected>Rapat Pimpinan (Dekanat)</option>
                                <option value="Rapat Kaprodi">Rapat Koordinasi Kaprodi</option>
                                <option value="Rapat Dosen Prodi">Rapat Dosen Program Studi</option>
                                <option value="Rapat Gabungan">Rapat Gabungan</option>
                                <option value="Rapat Senat">Rapat Pleno Senat Fakultas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lokasi / Ruang Rapat</label>
                            <input type="text" class="form-control" name="location" placeholder="Ruang Rapat Dekanat Lt. 3" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Pelaksanaan</label>
                            <input type="date" class="form-control" name="meeting_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Waktu Mulai</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pimpinan Rapat</label>
                            <select name="chairperson_id" class="form-select">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $u['role_slug'] === 'dekan' ? 'selected' : '' ?>><?= $u['name'] ?> (<?= $u['role_name'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Notulis / Sekretaris Rapat</label>
                            <select name="secretary_id" class="form-select">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= $u['name'] ?> (<?= $u['role_name'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Agenda & Pembahasan Utama</label>
                        <textarea name="agenda" class="form-control" rows="4" placeholder="1. Pembahasan serapan anggaran...&#10;2. Evaluasi BKD dosen...&#10;3. Kesiapan akreditasi LAM-INFOKOM..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $baseUrl ?>/meetings" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ti ti-calendar-plus me-1"></i> Buat & Buka Paket Digital
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
