<?php use App\Helpers\CsrfHelper; ?>
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card card-lg shadow-sm border-0 text-center p-4">
            <div class="card-body">
                <div class="avatar avatar-xxl bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-1 shadow">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-muted small mb-2">@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="badge bg-primary-subtle text-primary px-3 py-1"><?= strtoupper($currentRole) ?></span>
                
                <hr class="my-4">
                
                <div class="text-start small text-muted">
                    <div class="mb-2"><i class="ti ti-mail me-2"></i><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="mb-2"><i class="ti ti-phone me-2"></i><?= htmlspecialchars($user['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                    <div><i class="ti ti-id me-2"></i>NIDN: <?= htmlspecialchars($user['nidn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card card-lg shadow-sm border-0">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">Edit Profil & Keamanan Akun</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= $baseUrl ?>/profile" method="POST">
                    <?= CsrfHelper::tokenField() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Ganti Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="new_password" placeholder="Masukkan password baru...">
                    </div>

                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
