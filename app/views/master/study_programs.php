<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-school text-primary fs-2"></i> Data Master Program Studi
        </h3>
        <p class="text-muted small mb-0">Kelola daftar jurusan, program studi, status akreditasi, ketua prodi, dan metrik kapasitas mahasiswa.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary shadow-sm btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddProgram">
            <i class="ti ti-plus"></i> Tambah Program Studi Baru
        </button>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Program Studi Fakultas</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($programs) ?> Program Studi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode & Jenjang</th>
                        <th>Program Studi</th>
                        <th>Ketua Program Studi (Kaprodi)</th>
                        <th>Akreditasi & Skor</th>
                        <th>Kapasitas Mahasiswa</th>
                        <th>Jumlah Dosen</th>
                        <th>Target Retensi</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($p['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div><span class="badge bg-primary-subtle text-primary"><?= $p['degree'] ?></span></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($p['faculty_name'] ?? 'Fakultas Teknologi Informasi dan Komputer', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($p['head_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <?= FormatHelper::statusBadge($p['accreditation_status']) ?>
                                <small class="d-block text-muted mt-1">Skor: <strong><?= $p['accreditation_score'] ?></strong></small>
                            </td>
                            <td><strong><?= number_format($p['student_count']) ?></strong> Mahasiswa</td>
                            <td><strong><?= number_format($p['lecturer_count']) ?></strong> Dosen</td>
                            <td><strong><?= $p['target_retention'] ?>%</strong></td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditProgram<?= $p['id'] ?>" title="Edit Program Studi">
                                        <i class="ti ti-edit"></i> Edit
                                    </button>
                                    <form action="<?= $baseUrl ?>/master/study-programs/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program studi <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-square" title="Hapus Prodi">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Program Studi -->
                                <div class="modal fade" id="modalEditProgram<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $baseUrl ?>/master/study-programs/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Program Studi: <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Kode Prodi</label>
                                                            <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($p['code'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label small fw-semibold">Jenjang Pendidikan</label>
                                                            <select name="degree" class="form-select" required>
                                                                <option value="D3" <?= $p['degree'] === 'D3' ? 'selected' : '' ?>>Diploma 3 (D3)</option>
                                                                <option value="S1" <?= $p['degree'] === 'S1' ? 'selected' : '' ?>>Sarjana (S1)</option>
                                                                <option value="S2" <?= $p['degree'] === 'S2' ? 'selected' : '' ?>>Magister (S2)</option>
                                                                <option value="S3" <?= $p['degree'] === 'S3' ? 'selected' : '' ?>>Doktor (S3)</option>
                                                                <option value="Profesi" <?= $p['degree'] === 'Profesi' ? 'selected' : '' ?>>Profesi</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Nama Program Studi</label>
                                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Ketua Program Studi (Kaprodi)</label>
                                                        <input type="text" class="form-control" name="head_name" value="<?= htmlspecialchars($p['head_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Kaprodi & Gelar">
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Status Akreditasi</label>
                                                            <select name="accreditation_status" class="form-select">
                                                                <option value="Unggul" <?= $p['accreditation_status'] === 'Unggul' ? 'selected' : '' ?>>Unggul</option>
                                                                <option value="Baik Sekali" <?= $p['accreditation_status'] === 'Baik Sekali' ? 'selected' : '' ?>>Baik Sekali</option>
                                                                <option value="Baik" <?= $p['accreditation_status'] === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                                                <option value="A" <?= $p['accreditation_status'] === 'A' ? 'selected' : '' ?>>A</option>
                                                                <option value="B" <?= $p['accreditation_status'] === 'B' ? 'selected' : '' ?>>B</option>
                                                                <option value="C" <?= $p['accreditation_status'] === 'C' ? 'selected' : '' ?>>C</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Skor Akreditasi</label>
                                                            <input type="number" class="form-control" name="accreditation_score" value="<?= $p['accreditation_score'] ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Target Retensi (%)</label>
                                                            <input type="number" step="0.1" class="form-control" name="target_retention" value="<?= $p['target_retention'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Jml Mahasiswa</label>
                                                            <input type="number" class="form-control" name="student_count" value="<?= $p['student_count'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Jml Dosen</label>
                                                            <input type="number" class="form-control" name="lecturer_count" value="<?= $p['lecturer_count'] ?>">
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

<!-- Modal Tambah Program Studi Baru -->
<div class="modal fade" id="modalAddProgram" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/master/study-programs/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Program Studi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kode Prodi</label>
                            <input type="text" class="form-control" name="code" placeholder="TI, SI, SK" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Jenjang Pendidikan</label>
                            <select name="degree" class="form-select" required>
                                <option value="S1" selected>Sarjana (S1)</option>
                                <option value="S2">Magister (S2)</option>
                                <option value="S3">Doktor (S3)</option>
                                <option value="D3">Diploma 3 (D3)</option>
                                <option value="Profesi">Profesi</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Program Studi</label>
                        <input type="text" class="form-control" name="name" placeholder="contoh: Teknik Informatika" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ketua Program Studi (Kaprodi)</label>
                        <input type="text" class="form-control" name="head_name" placeholder="Nama Kaprodi & Gelar">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Akreditasi</label>
                            <select name="accreditation_status" class="form-select">
                                <option value="Unggul">Unggul</option>
                                <option value="Baik Sekali" selected>Baik Sekali</option>
                                <option value="Baik">Baik</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Skor Akreditasi</label>
                            <input type="number" class="form-control" name="accreditation_score" value="320">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Target Retensi (%)</label>
                            <input type="number" step="0.1" class="form-control" name="target_retention" value="85.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jml Mahasiswa</label>
                            <input type="number" class="form-control" name="student_count" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jml Dosen</label>
                            <input type="number" class="form-control" name="lecturer_count" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Program Studi</button>
                </div>
            </form>
        </div>
    </div>
</div>
