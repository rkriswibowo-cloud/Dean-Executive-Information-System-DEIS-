<?php
use App\Helpers\FormatHelper;
?>

<!-- 1. Executive Welcome & My Attention Banner -->
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
        <div class="bg-gradient-mixed p-4 p-lg-5 rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="position-relative z-1">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-75 text-primary mb-3 small fw-semibold">
                    <i class="ti ti-sparkles"></i> Executive Overview • Semester Genap 2025/2026
                </div>
                <h2 class="fw-bold fs-2 text-dark mb-1">Selamat Datang, <?= htmlspecialchars($currentUser['name'] ?? 'Dekan', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-secondary mb-4" style="max-width: 580px;">
                    Pusat kendali eksekutif <?= htmlspecialchars($faculty['name'] ?? 'Fakultas', ENT_QUOTES, 'UTF-8') ?>. Pantau operasional fakultas, identifikasi peringatan kritis, dan tindak lanjuti keputusan strategis secara real-time.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= $baseUrl ?>/command-center" class="btn btn-dark shadow-sm d-flex align-items-center gap-2 px-3">
                        <i class="ti ti-radar text-danger fs-5"></i> Buka Command Center
                    </a>
                    <a href="<?= $baseUrl ?>/meetings/create" class="btn btn-white shadow-sm d-flex align-items-center gap-2 px-3">
                        <i class="ti ti-calendar-plus text-primary fs-5"></i> Jadwalkan Rapat
                    </a>
                    <a href="<?= $baseUrl ?>/reports" class="btn btn-outline-dark d-flex align-items-center gap-2 px-3">
                        <i class="ti ti-printer fs-5"></i> Cetak Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- My Attention Summary Card (PRD Section 8.6) -->
    <div class="col-12 col-xl-4">
        <div class="card card-lg shadow-sm border-0 h-100 rounded-4">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="ti ti-bell-ringing text-danger"></i> My Attention
                    </h5>
                    <span class="badge bg-danger rounded-pill px-3 py-1 fs-6"><?= $attention['total'] ?> Item</span>
                </div>
                <p class="small text-muted mb-3">Daftar item prioritas yang memerlukan intervensi dan persetujuan Dekan saat ini:</p>

                <div class="d-flex flex-column gap-2 mb-3">
                    <a href="<?= $baseUrl ?>/command-center/approvals" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light text-decoration-none text-dark hover-bg">
                        <span class="small d-flex align-items-center gap-2">
                            <i class="ti ti-checklist text-warning fs-5"></i> Pending Approvals
                        </span>
                        <span class="badge bg-warning text-dark fw-bold"><?= $attention['pending_approvals'] ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/meetings/rtl" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light text-decoration-none text-dark hover-bg">
                        <span class="small d-flex align-items-center gap-2">
                            <i class="ti ti-alert-triangle text-danger fs-5"></i> RTL Terlambat
                        </span>
                        <span class="badge bg-danger fw-bold"><?= $attention['overdue_rtl'] ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/lecturers?bkd_status=Belum+Memenuhi" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light text-decoration-none text-dark hover-bg">
                        <span class="small d-flex align-items-center gap-2">
                            <i class="ti ti-user-x text-warning fs-5"></i> Dosen Perlu Pembinaan
                        </span>
                        <span class="badge bg-warning text-dark fw-bold"><?= $attention['problematic_lecturers'] ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/students/early-warning" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light text-decoration-none text-dark hover-bg">
                        <span class="small d-flex align-items-center gap-2">
                            <i class="ti ti-school-bell text-danger fs-5"></i> Mahasiswa Berisiko DO
                        </span>
                        <span class="badge bg-danger fw-bold"><?= $attention['at_risk_students'] ?></span>
                    </a>
                    <a href="<?= $baseUrl ?>/accreditation" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light text-decoration-none text-dark hover-bg">
                        <span class="small d-flex align-items-center gap-2">
                            <i class="ti ti-clock-exclamation text-info fs-5"></i> Deadline Mendesak
                        </span>
                        <span class="badge bg-info text-white fw-bold"><?= $attention['urgent_deadlines'] ?></span>
                    </a>
                </div>

                <a href="<?= $baseUrl ?>/command-center" class="btn btn-outline-primary btn-sm w-100 py-2">
                    Buka Radar Command Center →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. Critical Alert Banner (If Any Critical Alert is Active) -->
<?php if (!empty($criticalAlerts)): ?>
<div class="alert alert-danger shadow-sm border-0 rounded-4 p-4 mb-4 d-flex align-items-start gap-3">
    <div class="avatar avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mt-1">
        <i class="ti ti-alert-triangle fs-4"></i>
    </div>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="alert-heading fw-bold mb-1 text-danger">Peringatan Kritis Memerlukan Tindakan Dekan</h5>
            <span class="badge bg-danger text-white"><?= count($criticalAlerts) ?> Alert Aktif</span>
        </div>
        <p class="mb-2 small text-dark"><?= htmlspecialchars($criticalAlerts[0]['title'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($criticalAlerts[0]['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= $baseUrl ?>/<?= $criticalAlerts[0]['target_url'] ?? 'command-center' ?>" class="btn btn-sm btn-danger px-3 py-1">
            Tangani Sekarang →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 3. Key Performance Indicators (Executive KPI Grid) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
    <!-- KPI 1: Mahasiswa Aktif -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Mahasiswa Aktif</span>
                    <div class="icon-shape-lg bg-primary-subtle text-primary rounded-circle">
                        <i class="ti ti-school fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= number_format($totalStudents) ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-success-subtle text-success"><i class="ti ti-trending-up me-1"></i>+4.2%</span>
                    <span class="text-muted">vs semester lalu</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Kepatuhan BKD Dosen -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Kepatuhan BKD Dosen</span>
                    <div class="icon-shape-lg bg-success-subtle text-success rounded-circle">
                        <i class="ti ti-user-check fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= $bkdCompliance ?>% <span class="fs-6 fw-normal text-muted">(<?= $totalLecturers ?> Dosen)</span></h3>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-<?= $bkdCompliance >= 85 ? 'success' : 'warning' ?>-subtle text-<?= $bkdCompliance >= 85 ? 'success' : 'warning' ?>">
                        <?= $bkdCompliance >= 85 ? 'Target Terpenuhi' : 'Perlu Pendampingan' ?>
                    </span>
                    <span class="text-muted">Target fakultas >= 85%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Rata-Rata Capaian IKU -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Capaian IKU Fakultas</span>
                    <div class="icon-shape-lg bg-info-subtle text-info rounded-circle">
                        <i class="ti ti-target-arrow fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= $avgIkuAchievement ?>% <span class="fs-6 fw-normal text-muted">Rata-rata</span></h3>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i>Baik Sekali</span>
                    <span class="text-muted">8 Indikator Kinerja Utama</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Rata-rata IPK -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Rata-rata IPK Mahasiswa</span>
                    <div class="icon-shape-lg bg-warning-subtle text-warning fs-3 rounded-circle">
                        <i class="ti ti-certificate fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= $avgGpa ?> <span class="fs-6 fw-normal text-muted">/ 4.00</span></h3>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-success-subtle text-success"><i class="ti ti-trending-up me-1"></i>Optimal</span>
                    <span class="text-muted">Standar SPMI >= 3.35</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 5: Serapan Anggaran RKA -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Serapan Anggaran RKA</span>
                    <div class="icon-shape-lg bg-success-subtle text-success rounded-circle">
                        <i class="ti ti-wallet fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= $budgetAbsorption ?>%</h3>
                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <span>Realisasi: <?= FormatHelper::currency($totalRealized) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 6: Status Akreditasi Prodi -->
    <div class="col">
        <div class="card card-lg shadow-sm border-0 rounded-4 kpi-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Status Akreditasi Prodi</span>
                    <div class="icon-shape-lg bg-primary-subtle text-primary rounded-circle">
                        <i class="ti ti-award fs-3"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">3 <span class="fs-6 fw-normal text-muted">Program Studi</span></h3>
                <div class="d-flex align-items-center gap-1 small">
                    <span class="badge bg-success-subtle text-success">1 Unggul</span>
                    <span class="badge bg-info-subtle text-info">1 Baik Sekali</span>
                    <span class="badge bg-warning-subtle text-warning">1 Baik</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Interactive Charts & Strategic Indicators Table -->
<div class="row g-4 mb-4">
    <!-- Chart: Capaian IKU 1-8 -->
    <div class="col-12 col-xl-7">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0">Capaian Indikator Kinerja Utama (IKU 1 - IKU 8)</h5>
                <a href="<?= $baseUrl ?>/strategic" class="btn btn-sm btn-outline-primary">Lihat Detail IKU →</a>
            </div>
            <div class="card-body p-4">
                <div id="ikuChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- Accreditations & Upcoming Meetings -->
    <div class="col-12 col-xl-5">
        <div class="card card-lg shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold mb-0">Agenda Dekan & Rapat Pimpinan</h5>
                <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">Semua Rapat</a>
            </div>
            <div class="card-body p-4">
                <?php if (empty($upcomingMeetings)): ?>
                    <p class="text-muted text-center py-4">Tidak ada agenda rapat terjadwal.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($upcomingMeetings as $m): ?>
                            <div class="p-3 rounded-3 bg-light border d-flex align-items-start gap-3">
                                <div class="avatar avatar-sm bg-primary text-white rounded-3 d-flex align-items-center justify-content-center fw-bold">
                                    <?= date('d', strtotime($m['meeting_date'])) ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="fw-semibold mb-1 text-truncate"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                                    <div class="small text-muted d-flex align-items-center gap-3">
                                        <span><i class="ti ti-clock me-1"></i><?= substr($m['start_time'], 0, 5) ?> WIB</span>
                                        <span><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($m['location'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <a href="<?= $baseUrl ?>/meetings/detail?id=<?= $m['id'] ?>" class="btn btn-sm btn-light border">Buka</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Script for Dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = document.body.getAttribute('data-base-url') || '';
    
    fetch(`${baseUrl}/dashboard/chart-data`)
        .then(res => res.json())
        .then(data => {
            const categories = data.ikuData.map(d => d.code);
            const seriesData = data.ikuData.map(d => parseFloat(d.achievement_percentage));

            const options = {
                series: [{
                    name: 'Capaian (%)',
                    data: seriesData
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '45%',
                        distributed: true
                    }
                },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#f97316', '#06b6d4', '#8b5cf6', '#ec4899', '#ef4444'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val + '%';
                    },
                    style: {
                        fontSize: '11px',
                        colors: ['#333']
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: { style: { fontWeight: 600 } }
                },
                yaxis: {
                    title: { text: 'Persentase Capaian Target' },
                    max: 120
                },
                legend: { show: false },
                grid: { borderColor: '#f1f5f9' }
            };

            const chart = new ApexCharts(document.querySelector("#ikuChart"), options);
            chart.render();
        });
});
</script>
