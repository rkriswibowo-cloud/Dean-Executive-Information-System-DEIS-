<?php use App\Helpers\CsrfHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                <img src="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg" alt="DEIS" width="40" height="40">
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= $appConfig['short_name'] ?></h3>
            <p class="text-muted small">Sistem Informasi Eksekutif Dekan Fakultas</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center mb-3">
                <i class="ti ti-alert-circle fs-5 me-2"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= $baseUrl ?>/login" method="POST">
            <?= CsrfHelper::tokenField() ?>
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-user text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" value="<?= htmlspecialchars($oldUsername ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                <i class="ti ti-login me-1"></i> Masuk ke Dashboard
            </button>
        </form>

        <div class="p-3 bg-light rounded-3 border mt-3 small">
            <div class="fw-semibold mb-1 text-dark">Demo Akun Cepat:</div>
            <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-xs btn-outline-primary" onclick="document.getElementById('username').value='dekan';document.getElementById('password').value='password';">Dekan (Eksekutif)</button>
                <button type="button" class="btn btn-xs btn-outline-info" onclick="document.getElementById('username').value='kaprodi.ti';document.getElementById('password').value='password';">Kaprodi TI</button>
                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="document.getElementById('username').value='admin';document.getElementById('password').value='password';">Super Admin</button>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('username').value='developer';document.getElementById('password').value='password';">Developer</button>
            </div>
        </div>
    </div>
</div>
