<?php use App\Helpers\CsrfHelper; ?>

<div class="card auth-card border-0 p-4 p-sm-5">
    <div class="card-body p-0">
        <!-- Brand Header -->
        <div class="text-center mb-4">
            <div class="auth-brand-logo mx-auto mb-3">
                <img src="<?= $baseUrl ?>/assets/images/brand/logo/logo-icon.svg" alt="DEIS Logo" width="34" height="34">
            </div>
            <div class="d-inline-flex align-items-center gap-1 badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 mb-2 rounded-pill small">
                <i class="ti ti-shield-check"></i>
                <span>Portal Masuk Eksekutif</span>
            </div>
            <h3 class="fw-bold text-dark mb-1 fs-4"><?= $appConfig['short_name'] ?></h3>
            <p class="text-muted small mb-0">Dean Executive Information System</p>
        </div>

        <!-- Alert Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2.5 px-3 small d-flex align-items-center mb-3 rounded-3 shadow-sm border-danger-subtle">
                <i class="ti ti-alert-circle fs-5 me-2 flex-shrink-0"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?= $baseUrl ?>/login" method="POST" autocomplete="on">
            <?= CsrfHelper::tokenField() ?>

            <!-- Username Field -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-bold text-body-secondary mb-1.5">
                    Username / ID Pengguna <span class="text-danger">*</span>
                </label>
                <div class="auth-input-group">
                    <span class="input-icon">
                        <i class="ti ti-user"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?= htmlspecialchars($oldUsername ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Contoh: dekan, kaprodi.ti, admin" 
                           required 
                           autofocus>
                </div>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <label for="password" class="form-label small fw-bold text-body-secondary mb-0">
                        Kata Sandi (Password) <span class="text-danger">*</span>
                    </label>
                </div>
                <div class="auth-input-group">
                    <span class="input-icon">
                        <i class="ti ti-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan kata sandi akun Anda" 
                           required>
                    <button type="button" class="btn-password-toggle" id="togglePasswordBtn" title="Tampilkan / Sembunyikan Kata Sandi" tabindex="-1">
                        <i class="ti ti-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Remember & Security Info -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                    <label class="form-check-label small text-muted user-select-none" for="rememberMe" style="font-size: 0.8rem;">
                        Ingat sesi saya
                    </label>
                </div>
                <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="ti ti-shield-lock text-success"></i>
                    <span>RBAC 256-Bit</span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 rounded-3 mb-4">
                <span>Masuk ke Dashboard</span>
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>

        <!-- Quick Demo Login Switcher -->
        <div class="pt-3 border-top">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    Akses Cepat Demo:
                </span>
                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Klik untuk isi otomatis</span>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <button type="button" class="auth-role-card" onclick="setDemoCredentials('dekan', 'password')">
                        <span class="avatar avatar-xs bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                            <i class="ti ti-crown" style="font-size: 0.85rem;"></i>
                        </span>
                        <div class="overflow-hidden text-truncate">
                            <strong class="d-block text-truncate text-dark" style="font-size: 0.775rem;">Dekan</strong>
                            <span class="text-muted d-block text-truncate" style="font-size: 0.68rem;">Eksekutif</span>
                        </div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="auth-role-card" onclick="setDemoCredentials('kaprodi.ti', 'password')">
                        <span class="avatar avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                            <i class="ti ti-school" style="font-size: 0.85rem;"></i>
                        </span>
                        <div class="overflow-hidden text-truncate">
                            <strong class="d-block text-truncate text-dark" style="font-size: 0.775rem;">Kaprodi TI</strong>
                            <span class="text-muted d-block text-truncate" style="font-size: 0.68rem;">Ketua Prodi</span>
                        </div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="auth-role-card" onclick="setDemoCredentials('admin', 'password')">
                        <span class="avatar avatar-xs bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                            <i class="ti ti-adjustments" style="font-size: 0.85rem;"></i>
                        </span>
                        <div class="overflow-hidden text-truncate">
                            <strong class="d-block text-truncate text-dark" style="font-size: 0.775rem;">Super Admin</strong>
                            <span class="text-muted d-block text-truncate" style="font-size: 0.68rem;">Administrator</span>
                        </div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="auth-role-card" onclick="setDemoCredentials('developer', 'password')">
                        <span class="avatar avatar-xs bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                            <i class="ti ti-code" style="font-size: 0.85rem;"></i>
                        </span>
                        <div class="overflow-hidden text-truncate">
                            <strong class="d-block text-truncate text-dark" style="font-size: 0.775rem;">Developer</strong>
                            <span class="text-muted d-block text-truncate" style="font-size: 0.68rem;">Konsol Pengembang</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Caption -->
        <div class="text-center mt-4">
            <small class="text-muted" style="font-size: 0.725rem;">
                © <?= date('Y') ?> <?= $appConfig['short_name'] ?> • Sistem Informasi Eksekutif Dekan
            </small>
        </div>
    </div>
</div>

<script>
function setDemoCredentials(username, password) {
    const userField = document.getElementById('username');
    const passField = document.getElementById('password');
    if (userField && passField) {
        userField.value = username;
        passField.value = password;
        userField.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && passInput && toggleIcon) {
        toggleBtn.addEventListener('click', function() {
            if (passInput.type === 'password') {
                passInput.type = 'text';
                toggleIcon.classList.remove('ti-eye');
                toggleIcon.classList.add('ti-eye-off');
            } else {
                passInput.type = 'password';
                toggleIcon.classList.remove('ti-eye-off');
                toggleIcon.classList.add('ti-eye');
            }
        });
    }
});
</script>
