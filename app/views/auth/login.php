<?php use App\Helpers\CsrfHelper; ?>

<div class="deis-login-split">
    <section class="deis-form-panel">
        <div class="deis-form-wrap">
            <div class="deis-brand deis-reveal d1">
                <span class="deis-brand-icon"><i class="fa-solid fa-building-columns"></i></span>
                <span><strong>DEIS</strong><small>Dean Executive Information System</small></span>
            </div>

            <header class="deis-heading">
                <h1 class="deis-reveal d2"><span>Selamat datang</span> kembali</h1>
                <p class="deis-reveal d3">Akses pusat kendali untuk memantau kinerja dan tata kelola fakultas secara terpadu.</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="deis-alert deis-reveal d3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/login" method="POST" autocomplete="on" class="deis-login-form">
                <?= CsrfHelper::tokenField() ?>

                <div class="deis-field deis-reveal d4">
                    <label for="username">Username / ID Pengguna</label>
                    <div class="deis-glass-input">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($oldUsername ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Masukkan username dekan" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="deis-field deis-reveal d5">
                    <label for="password">Password</label>
                    <div class="deis-glass-input">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" id="togglePasswordBtn" class="deis-password-toggle" title="Tampilkan password" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="deis-form-meta deis-reveal d6">
                    <span><i class="fa-solid fa-shield-halved text-primary"></i> Portal Dekan & Super Admin</span>
                    <button type="button" onclick="showDeisLoginHelp()">Bantuan login</button>
                </div>

                <button type="submit" class="deis-submit deis-reveal d7">
                    <span>Masuk ke Sistem DEIS</span><i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="deis-demo deis-reveal d8">
                <div class="deis-demo-head"><span>Kredensial Akses Cepat</span><small>Klik untuk mengisi otomatis</small></div>
                <div class="deis-demo-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                    <button type="button" onclick="setDemoCredentials('dekan', 'password')" class="w-100 text-start">
                        <i class="fa-solid fa-crown text-warning"></i>
                        <span><strong>Dekan (Eksekutif)</strong><small>Username: dekan · Pass: password</small></span>
                    </button>
                    <button type="button" onclick="setDemoCredentials('admin', 'password')" class="w-100 text-start">
                        <i class="fa-solid fa-user-shield text-primary"></i>
                        <span><strong>Super Admin (Sistem)</strong><small>Username: admin · Pass: password</small></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="deis-visual-panel" aria-label="Pusat kendali eksekutif fakultas">
        <div class="deis-visual-card">
            <div class="deis-live deis-slide d2"><span></span> Executive Command Center</div>
            <div class="deis-visual-copy deis-slide d3">
                <span>DATA-DRIVEN LEADERSHIP</span>
                <h2>Satu data.<br>Keputusan bermakna.</h2>
                <p>Ringkasan akademik, mutu, keuangan, dan indikator strategis untuk pengambilan keputusan pimpinan.</p>
            </div>

            <div class="deis-dashboard-card deis-board d5">
                <div class="deis-board-head">
                    <div><small>Executive Overview</small><strong>Kinerja Fakultas</strong></div>
                    <span><i class="fa-solid fa-arrow-trend-up"></i> Semester Aktif</span>
                </div>
                <div class="deis-kpi-grid">
                    <div><i class="fa-solid fa-users"></i><span><small>Mahasiswa Aktif</small><strong>1.248</strong></span></div>
                    <div><i class="fa-solid fa-user-tie"></i><span><small>Dosen</small><strong>64</strong></span></div>
                    <div><i class="fa-solid fa-chart-line"></i><span><small>Capaian IKU</small><strong>86%</strong></span></div>
                </div>
                <div class="deis-chart">
                    <div class="deis-chart-label"><span>Tren kinerja fakultas</span><strong>+12,4%</strong></div>
                    <svg viewBox="0 0 520 120" preserveAspectRatio="none" aria-hidden="true">
                        <defs><linearGradient id="deisChartFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#818cf8" stop-opacity=".4"/><stop offset="1" stop-color="#818cf8" stop-opacity="0"/></linearGradient></defs>
                        <path d="M0 100 C55 88 68 92 110 70 S180 82 225 55 S292 72 340 38 S420 55 520 12 L520 120 L0 120 Z" fill="url(#deisChartFill)"/>
                        <path d="M0 100 C55 88 68 92 110 70 S180 82 225 55 S292 72 340 38 S420 55 520 12" fill="none" stroke="#818cf8" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            <div class="deis-feature-chips deis-board d7">
                <span><i class="fa-solid fa-bullseye"></i> IKU & Renstra</span>
                <span><i class="fa-solid fa-award"></i> Akreditasi</span>
                <span><i class="fa-solid fa-file-signature"></i> Persetujuan</span>
            </div>
        </div>
    </section>
</div>

<script>
function setDemoCredentials(username, password) {
    const user = document.getElementById('username');
    const pass = document.getElementById('password');
    if (!user || !pass) return;
    user.value = username;
    pass.value = password;
    user.focus();
}

function showDeisLoginHelp() {
    alert('Sistem DEIS dapat diakses menggunakan akun Dekan (Username: dekan) atau Super Admin (Username: admin). Masukkan kata sandi atau klik tombol kredensial akses cepat.');
}

document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('togglePasswordBtn');
    const input = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (!button || !input || !icon) return;
    button.addEventListener('click', function () {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        button.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
});
</script>
