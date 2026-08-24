<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-building text-primary fs-2"></i> Data Master Fakultas
        </h3>
        <p class="text-muted small mb-0">Kelola daftar fakultas, kode, pimpinan Dekan, visi, misi, dan struktur program studi.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary shadow-sm btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddFaculty">
            <i class="ti ti-plus"></i> Tambah Fakultas Baru
        </button>
    </div>
</div>

<!-- List of Faculties Table -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Fakultas Universitas</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($faculties) ?> Fakultas Terdaftar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Fakultas</th>
                        <th>Dekan Pimpinan</th>
                        <th>Program Studi</th>
                        <th>Total Mahasiswa</th>
                        <th>Total Dosen</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faculties as $f): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-primary fs-6 px-3 py-1"><?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted text-truncate d-block" style="max-width: 320px;">
                                    <strong>Visi:</strong> <?= htmlspecialchars($f['vision'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($f['dean_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <strong><?= $f['study_programs_count'] ?? 0 ?></strong> Prodi
                            </td>
                            <td>
                                <strong><?= number_format($f['total_students'] ?? 0) ?></strong> Mahasiswa
                            </td>
                            <td>
                                <strong><?= number_format($f['total_lecturers'] ?? 0) ?></strong> Dosen
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditFaculty<?= $f['id'] ?>" title="Edit Profil Fakultas">
                                        <i class="ti ti-edit"></i> Edit
                                    </button>
                                    <?php if ($f['id'] > 1): ?>
                                        <form action="<?= $baseUrl ?>/master/faculties/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fakultas <?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>?');">
                                            <?= CsrfHelper::tokenField() ?>
                                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-square" title="Hapus Fakultas">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <!-- Modal Edit Fakultas -->
                                <div class="modal fade" id="modalEditFaculty<?= $f['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                        <div class="modal-content border-0 shadow">
                                            <form action="<?= $baseUrl ?>/master/faculties/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Profil Fakultas: <?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold">Kode Fakultas</label>
                                                            <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label small fw-semibold">Nama Resmi Fakultas</label>
                                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Nama Lengkap Dekan & Gelar</label>
                                                        <input type="text" class="form-control" name="dean_name" value="<?= htmlspecialchars($f['dean_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Visi Fakultas</label>
                                                        <textarea name="vision" class="form-control" rows="3" required><?= htmlspecialchars($f['vision'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Misi Fakultas</label>
                                                        <textarea name="mission" class="form-control" rows="5" required><?= htmlspecialchars($f['mission'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan & Sinkronkan</button>
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

<!-- Modal Tambah Fakultas Baru -->
<div class="modal fade" id="modalAddFaculty" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/master/faculties/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Fakultas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kode Fakultas</label>
                            <input type="text" class="form-control" name="code" placeholder="FEB, FT, FK, dll." required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Resmi Fakultas</label>
                            <input type="text" class="form-control" name="name" placeholder="contoh: Fakultas Ekonomi dan Bisnis" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap Dekan & Gelar</label>
                        <input type="text" class="form-control" name="dean_name" placeholder="contoh: Prof. Dr. Budi Santoso, S.E., M.M." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Visi Fakultas</label>
                        <textarea name="vision" class="form-control" rows="3" placeholder="Rumusan visi strategis fakultas..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Misi Fakultas</label>
                        <textarea name="mission" class="form-control" rows="4" placeholder="1. Menyelenggarakan pendidikan...&#10;2. Melakukan penelitian..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Fakultas Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
