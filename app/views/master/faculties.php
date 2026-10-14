<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-body mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-building-columns text-primary"></i> Data Master Fakultas
        </h4>
        <p class="text-muted small mb-0">Kelola daftar fakultas, kode, pimpinan Dekan, visi, misi, dan struktur program studi.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddFaculty">
            <i class="fa-solid fa-plus"></i> Tambah Fakultas Baru
        </button>
    </div>
</div>

<!-- List of Faculties Table -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-list text-primary"></i> Daftar Fakultas Universitas
        </h5>
        <span class="badge badge-soft-primary px-3 py-2"><?= count($faculties) ?> Fakultas Terdaftar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
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
                                <span class="badge badge-soft-primary font-monospace fs-6 px-3 py-1"><?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-body fs-6"><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted text-truncate d-block" style="max-width: 320px;">
                                    <strong>Visi:</strong> <?= htmlspecialchars($f['vision'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-body"><i class="fa-solid fa-user-tie text-muted me-1"></i><?= htmlspecialchars($f['dean_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <strong class="text-body"><?= $f['study_programs_count'] ?? 0 ?></strong> <span class="text-muted small">Prodi</span>
                            </td>
                            <td>
                                <strong class="text-body"><?= number_format($f['total_students'] ?? 0) ?></strong> <span class="text-muted small">Mhs</span>
                            </td>
                            <td>
                                <strong class="text-body"><?= number_format($f['total_lecturers'] ?? 0) ?></strong> <span class="text-muted small">Dosen</span>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-soft-primary rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalEditFaculty<?= $f['id'] ?>" title="Edit Profil Fakultas">
                                        <i class="fa-solid fa-pen-to-square"></i> <span>Edit</span>
                                    </button>
                                    <?php if ($f['id'] > 1): ?>
                                        <form action="<?= $baseUrl ?>/master/faculties/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fakultas <?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>?');">
                                            <?= CsrfHelper::tokenField() ?>
                                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-3 px-2 py-1" title="Hapus Fakultas">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <!-- Modal Edit Fakultas -->
                                <div class="modal fade" id="modalEditFaculty<?= $f['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="<?= $baseUrl ?>/master/faculties/update" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                                                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Profil Fakultas: <?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-semibold text-muted">Kode Fakultas</label>
                                                            <input type="text" class="form-control rounded-3" name="code" value="<?= htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label small fw-semibold text-muted">Nama Resmi Fakultas</label>
                                                            <input type="text" class="form-control rounded-3" name="name" value="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Dekan & Gelar</label>
                                                        <input type="text" class="form-control rounded-3" name="dean_name" value="<?= htmlspecialchars($f['dean_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Visi Fakultas</label>
                                                        <textarea name="vision" class="form-control rounded-3" rows="3" required><?= htmlspecialchars($f['vision'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Misi Fakultas</label>
                                                        <textarea name="mission" class="form-control rounded-3" rows="5" required><?= htmlspecialchars($f['mission'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan & Sinkronkan
                                                    </button>
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
        <div class="modal-content border-0 shadow-lg rounded-4 text-start">
            <form action="<?= $baseUrl ?>/master/faculties/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header bg-transparent border-bottom border-light-subtle py-3 px-4">
                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building-columns text-primary"></i> Tambah Fakultas Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Kode Fakultas</label>
                            <input type="text" class="form-control rounded-3" name="code" placeholder="FEB, FT, FK, dll." required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">Nama Resmi Fakultas</label>
                            <input type="text" class="form-control rounded-3" name="name" placeholder="contoh: Fakultas Ekonomi dan Bisnis" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Dekan & Gelar</label>
                        <input type="text" class="form-control rounded-3" name="dean_name" placeholder="contoh: Prof. Dr. Budi Santoso, S.E., M.M." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Visi Fakultas</label>
                        <textarea name="vision" class="form-control rounded-3" rows="3" placeholder="Rumusan visi strategis fakultas..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Misi Fakultas</label>
                        <textarea name="mission" class="form-control rounded-3" rows="4" placeholder="1. Menyelenggarakan pendidikan...&#10;2. Melakukan penelitian..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-transparent border-top border-light-subtle py-3 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Fakultas Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

