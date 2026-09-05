<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\AuthHelper;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-user-shield text-primary fs-2"></i> Manajemen Pengguna & Hak Akses (RBAC)
        </h3>
        <p class="text-muted small mb-0">Kelola akun pengguna, peran sistem (Dekan, Kaprodi, Dosen, SPMI, Operator, Admin), dan status akses.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary shadow-sm btn-crud" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="ti ti-user-plus"></i> Tambah Pengguna Baru
        </button>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Pengguna Sistem</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($users) ?> Pengguna</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Nama Lengkap & NIDN</th>
                        <th>Username</th>
                        <th>Email & Kontak</th>
                        <th>Peran (Role)</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <small class="text-muted"><?= !empty($u['nidn']) ? 'NIDN: ' . $u['nidn'] : 'Pengguna Non-Dosen' ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">@<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <div><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($u['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= FormatHelper::statusBadge($u['status'] === 'active' ? 'Aktif' : 'Non-Aktif') ?></td>
                            <td>
                                <small class="text-muted"><?= FormatHelper::timeAgo($u['last_login_at']) ?></small>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions d-flex align-items-center justify-content-end gap-1">
                                    <?php if ((int)$u['id'] !== (int)AuthHelper::id() && AuthHelper::isSuperAdmin()): ?>
                                        <form action="<?= $baseUrl ?>/impersonate" method="POST" class="d-inline">
                                            <?= CsrfHelper::tokenField() ?>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary btn-crud-sm" title="Masuk sebagai <?= htmlspecialchars($u['name']) ?> (Impersonate)">
                                                <i class="ti ti-mask"></i> Impersonate
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?= $baseUrl ?>/users/toggle-status" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                                        <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-crud-sm" onclick="return confirm('Ubah status aktif akun ini?');" title="<?= $u['status'] === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                            <i class="ti <?= $u['status'] === 'active' ? 'ti-user-x' : 'ti-user-check' ?>"></i>
                                            <?= $u['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/users/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                        <input type="text" class="form-control" name="name" placeholder="contoh: Dr. Rony Setiawan, M.T." required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="rony.setiawan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="email@deis.ac.id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" name="phone" placeholder="08123456789">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Peran (Role Akses)</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= $r['name'] ?> (<?= $r['slug'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
