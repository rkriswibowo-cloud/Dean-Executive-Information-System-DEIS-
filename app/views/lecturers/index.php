<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-users text-primary fs-2"></i> Data Master SDM & Kinerja Dosen
        </h3>
        <p class="text-muted small mb-0">Kelola biodata dosen, jabatan fungsional, kualifikasi pendidikan, kepatuhan BKD, dan capaian Tri Dharma.</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <a href="<?= $baseUrl ?>/lecturers/kpi" class="btn btn-outline-warning shadow-sm btn-crud">
            <i class="ti ti-trophy"></i> Ranking KPI Dosen
        </a>
        <button type="button" class="btn btn-primary shadow-sm btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddLecturer">
            <i class="ti ti-user-plus"></i> Tambah Dosen Baru
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Dosen Fakultas</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $totalLecturers ?> <span class="fs-6 fw-normal text-muted">Dosen</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Kualifikasi S3 (Doktor)</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $s3Count ?> <span class="fs-6 fw-normal text-muted">(<?= round(($s3Count/($totalLecturers?:1))*100, 1) ?>%)</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Tersertifikasi Pendidik</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $certifiedCount ?> <span class="fs-6 fw-normal text-muted">Dosen</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Publikasi & PkM</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $totalPubs ?> Pub / <?= $totalPkm ?> PkM</h3>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Dosen Fakultas</h5>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/lecturers?program_id=' + this.value">
                <option value="">Semua Program Studi</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Nama Dosen & NIDN</th>
                        <th>Jabatan & Pendidikan</th>
                        <th>Program Studi</th>
                        <th>Beban SKS</th>
                        <th>Status BKD</th>
                        <th>Presensi</th>
                        <th>Publikasi & SINTA</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lecturers as $l): ?>
                        <tr class="<?= $l['bkd_status'] === 'Belum Memenuhi' || $l['attendance_percentage'] < 75 ? 'table-warning' : '' ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted">NIDN: <?= $l['nidn'] ?> <?= !empty($l['email']) ? '• ' . $l['email'] : '' ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1"><?= $l['academic_rank'] ?></span>
                                <div class="small fw-semibold"><?= $l['education_level'] ?> • <?= $l['certification_status'] ?></div>
                            </td>
                            <td><?= htmlspecialchars($l['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= $l['teaching_load_sks'] ?></strong> SKS</td>
                            <td><?= FormatHelper::statusBadge($l['bkd_status']) ?></td>
                            <td><strong><?= $l['attendance_percentage'] ?>%</strong></td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-primary-subtle text-primary">SINTA: <?= $l['sinta_score'] ?></span>
                                    <span class="badge bg-info-subtle text-info">Pub: <?= $l['publication_count'] ?></span>
                                </div>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalBkdAction<?= $l['id'] ?>" title="Pengesahan BKD Dekanat">
                                        <i class="ti ti-gavel"></i> Aksi BKD
                                    </button>
                                    <a href="<?= $baseUrl ?>/lecturers/detail?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-info btn-crud-sm" title="Lihat Profil Dosen">
                                        <i class="ti ti-eye"></i> Detail
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon-square" data-bs-toggle="modal" data-bs-target="#modalEditLecturer<?= $l['id'] ?>" title="Edit Data Dosen">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form action="<?= $baseUrl ?>/lecturers/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dosen <?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-square" title="Hapus Dosen">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Aksi BKD Dekanat -->
                                <div class="modal fade" id="modalBkdAction<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-gavel text-warning"></i> Pengesahan Status BKD Dekanat
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/lecturers/action-bkd" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="lecturer_id" value="<?= $l['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                        <div class="small text-muted">NIDN: <?= $l['nidn'] ?> | Beban: <?= $l['teaching_load_sks'] ?> SKS | Presensi: <?= $l['attendance_percentage'] ?>%</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Keputusan Status BKD</label>
                                                        <select name="bkd_status" class="form-select" required>
                                                            <option value="Memenuhi" <?= $l['bkd_status'] === 'Memenuhi' ? 'selected' : '' ?>>✅ Memenuhi (Sahkan BKD Semester Ini)</option>
                                                            <option value="Belum Memenuhi" <?= $l['bkd_status'] === 'Belum Memenuhi' ? 'selected' : '' ?>>⚠️ Belum Memenuhi (Keluarkan Surat Teguran Dekan)</option>
                                                            <option value="Dalam Penilaian" <?= $l['bkd_status'] === 'Dalam Penilaian' ? 'selected' : '' ?>>🔵 Dalam Penilaian / Verifikasi Asesor</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan Evaluasi / Rekomendasi Dekan</label>
                                                        <textarea name="action_notes" class="form-control" rows="3" placeholder="Contoh: Disahkan dengan catatan peningkatan publikasi pada jurnal bereputasi di semester depan..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-semibold">
                                                        <i class="ti ti-check me-1"></i> Sahkan Status BKD
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Edit Dosen -->
                                <div class="modal fade" id="modalEditLecturer<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $baseUrl ?>/lecturers/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Data Dosen: <?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Program Studi</label>
                                                            <select name="study_program_id" class="form-select" required>
                                                                <?php foreach ($programs as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= $l['study_program_id'] == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">NIDN</label>
                                                            <input type="text" class="form-control" name="nidn" value="<?= htmlspecialchars($l['nidn'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">NIP / NUPTK</label>
                                                            <input type="text" class="form-control" name="nip" value="<?= htmlspecialchars($l['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Jabatan Fungsional</label>
                                                            <select name="academic_rank" class="form-select">
                                                                <option value="Tenaga Pengajar" <?= $l['academic_rank'] === 'Tenaga Pengajar' ? 'selected' : '' ?>>Tenaga Pengajar</option>
                                                                <option value="Asisten Ahli" <?= $l['academic_rank'] === 'Asisten Ahli' ? 'selected' : '' ?>>Asisten Ahli</option>
                                                                <option value="Lektor" <?= $l['academic_rank'] === 'Lektor' ? 'selected' : '' ?>>Lektor</option>
                                                                <option value="Lektor Kepala" <?= $l['academic_rank'] === 'Lektor Kepala' ? 'selected' : '' ?>>Lektor Kepala</option>
                                                                <option value="Guru Besar" <?= $l['academic_rank'] === 'Guru Besar' ? 'selected' : '' ?>>Guru Besar (Profesor)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Pendidikan Terakhir</label>
                                                            <select name="education_level" class="form-select">
                                                                <option value="S2" <?= $l['education_level'] === 'S2' ? 'selected' : '' ?>>S2 (Magister)</option>
                                                                <option value="S3" <?= $l['education_level'] === 'S3' ? 'selected' : '' ?>>S3 (Doktor)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Sertifikasi Dosen</label>
                                                            <select name="certification_status" class="form-select">
                                                                <option value="Tersertifikasi" <?= $l['certification_status'] === 'Tersertifikasi' ? 'selected' : '' ?>>Tersertifikasi</option>
                                                                <option value="Belum" <?= $l['certification_status'] === 'Belum' ? 'selected' : '' ?>>Belum Tersertifikasi</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Status Kepatuhan BKD</label>
                                                            <select name="bkd_status" class="form-select">
                                                                <option value="Memenuhi" <?= $l['bkd_status'] === 'Memenuhi' ? 'selected' : '' ?>>Memenuhi</option>
                                                                <option value="Belum Memenuhi" <?= $l['bkd_status'] === 'Belum Memenuhi' ? 'selected' : '' ?>>Belum Memenuhi</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Beban SKS</label>
                                                            <input type="number" class="form-control" name="teaching_load_sks" value="<?= $l['teaching_load_sks'] ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Presensi (%)</label>
                                                            <input type="number" step="0.1" class="form-control" name="attendance_percentage" value="<?= $l['attendance_percentage'] ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Skor SINTA</label>
                                                            <input type="number" class="form-control" name="sinta_score" value="<?= $l['sinta_score'] ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Scopus H-Index</label>
                                                            <input type="number" class="form-control" name="scopus_h_index" value="<?= $l['scopus_h_index'] ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Email Resmi</label>
                                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($l['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Nomor Telepon / WA</label>
                                                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($l['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Status Kepegawaian</label>
                                                            <select name="status" class="form-select">
                                                                <option value="Aktif" <?= $l['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                                                <option value="Tugas Belajar" <?= $l['status'] === 'Tugas Belajar' ? 'selected' : '' ?>>Tugas Belajar</option>
                                                                <option value="Cuti" <?= $l['status'] === 'Cuti' ? 'selected' : '' ?>>Cuti</option>
                                                                <option value="Pensiun" <?= $l['status'] === 'Pensiun' ? 'selected' : '' ?>>Pensiun</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Dosen Baru -->
<div class="modal fade" id="modalAddLecturer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/lecturers/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Dosen Baru Fakultas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                            <input type="text" class="form-control" name="name" placeholder="contoh: Dr. Ir. Ahmad Dahlan, M.Kom." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Program Studi</label>
                            <select name="study_program_id" class="form-select" required>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">NIDN</label>
                            <input type="text" class="form-control" name="nidn" placeholder="0012345678" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">NIP</label>
                            <input type="text" class="form-control" name="nip" placeholder="198001012005011001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jabatan Fungsional</label>
                            <select name="academic_rank" class="form-select">
                                <option value="Tenaga Pengajar">Tenaga Pengajar</option>
                                <option value="Asisten Ahli" selected>Asisten Ahli</option>
                                <option value="Lektor">Lektor</option>
                                <option value="Lektor Kepala">Lektor Kepala</option>
                                <option value="Guru Besar">Guru Besar</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenjang Pendidikan</label>
                            <select name="education_level" class="form-select">
                                <option value="S2" selected>S2 (Magister)</option>
                                <option value="S3">S3 (Doktor)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status Sertifikasi</label>
                            <select name="certification_status" class="form-select">
                                <option value="Tersertifikasi">Tersertifikasi</option>
                                <option value="Belum" selected>Belum</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status Kepatuhan BKD</label>
                            <select name="bkd_status" class="form-select">
                                <option value="Memenuhi" selected>Memenuhi</option>
                                <option value="Belum Memenuhi">Belum Memenuhi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Beban SKS</label>
                            <input type="number" class="form-control" name="teaching_load_sks" value="12">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Presensi (%)</label>
                            <input type="number" step="0.1" class="form-control" name="attendance_percentage" value="95.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Skor SINTA</label>
                            <input type="number" class="form-control" name="sinta_score" value="150">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Scopus H-Index</label>
                            <input type="number" class="form-control" name="scopus_h_index" value="2">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="dosen@deis.ac.id">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" name="phone" placeholder="08123456789">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status Dosen</label>
                            <select name="status" class="form-select">
                                <option value="Aktif" selected>Aktif</option>
                                <option value="Tugas Belajar">Tugas Belajar</option>
                                <option value="Cuti">Cuti</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data Dosen</button>
                </div>
            </form>
        </div>
    </div>
</div>
