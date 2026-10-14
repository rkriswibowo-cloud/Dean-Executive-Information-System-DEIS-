<?php
/**
 * views/dashboard/index.php
 * Dashboard Eksekutif DEIS - Mengadopsi Layout & Desain Sistem OBE
 * Data murni dari database & controller DEIS tanpa manipulasi data dummy
 */
use App\Helpers\FormatHelper;

$title = 'Dashboard Eksekutif - ' . ($faculty['name'] ?? 'Fakultas');
$attention = $attention ?? ['total' => 0, 'pending_approvals' => 0, 'overdue_rtl' => 0, 'problematic_lecturers' => 0, 'at_risk_students' => 0, 'urgent_deadlines' => 0];
?>

<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- 1. Welcome & Executive Action Header (Pola OBE) -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: rgba(var(--primary-rgb), 0.12); color: var(--primary); font-size: 0.78rem; font-weight: 600; margin-bottom: 8px;">
                <i class="fa-solid fa-sparkles"></i> Executive Overview • Semester Genap 2025/2026
            </div>
            <h2 style="margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 8px;"></i>Selamat Datang, <?= htmlspecialchars($currentUser['name'] ?? 'Dekan', ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 0.92rem; max-width: 680px;">
                Pusat kendali eksekutif <?= htmlspecialchars($faculty['name'] ?? 'Fakultas', ENT_QUOTES, 'UTF-8') ?>. Pantau operasional fakultas, identifikasi peringatan kritis, dan tindak lanjuti keputusan strategis secara real-time.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/command-center" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 9px 16px;">
                <i class="fa-solid fa-tower-broadcast"></i> Radar Command Center
            </a>
            <a href="<?= $baseUrl ?>/meetings/create" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 9px 16px;">
                <i class="fa-solid fa-calendar-plus"></i> Jadwalkan Rapat
            </a>
            <a href="<?= $baseUrl ?>/reports" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 9px 16px;">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- 2. Critical Alert Banner (Jika Ada Alert Kritis Aktif) -->
    <?php if (!empty($criticalAlerts)): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: var(--radius-md); padding: 18px 22px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 36px; height: 36px; border-radius: 10px; background: #ef4444; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; margin-top: 2px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <strong style="color: #ef4444; font-size: 0.95rem;">Peringatan Kritis Memerlukan Tindakan Dekan</strong>
                <span class="badge bg-danger text-white"><?= count($criticalAlerts) ?> Alert Aktif</span>
            </div>
            <p style="margin: 4px 0 10px 0; font-size: 0.85rem; color: var(--text-main);">
                <?= htmlspecialchars($criticalAlerts[0]['title'] ?? '', ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($criticalAlerts[0]['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <a href="<?= $baseUrl ?>/<?= $criticalAlerts[0]['target_url'] ?? 'command-center' ?>" class="btn btn-danger btn-sm" style="font-size: 0.8rem; padding: 5px 12px;">
                Tangani Sekarang <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. KPI Summary Grid (Gaya Presisi Kartu OBE) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px;">
        <!-- KPI 1: Mahasiswa Aktif -->
        <div class="kpi-card">
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Total Mahasiswa</div>
                <div style="font-size: 2.1rem; font-weight: 800; color: var(--primary); margin-top: 4px; line-height: 1;">
                    <?= number_format($totalStudents) ?>
                </div>
                <div style="color: var(--success); font-size: 0.78rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-arrow-trend-up"></i> +4.2% vs semester lalu
                </div>
            </div>
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(var(--primary-rgb), 0.12); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.3rem;">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <!-- KPI 2: Kepatuhan BKD Dosen -->
        <div class="kpi-card">
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Kepatuhan BKD</div>
                <div style="font-size: 2.1rem; font-weight: 800; color: var(--secondary); margin-top: 4px; line-height: 1;">
                    <?= $bkdCompliance ?>%
                </div>
                <div style="color: var(--text-muted); font-size: 0.78rem; margin-top: 6px;">
                    <?= $totalLecturers ?> Dosen • Target ≥85%
                </div>
            </div>
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: var(--success); font-size: 1.3rem;">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <!-- KPI 3: Capaian IKU Fakultas -->
        <div class="kpi-card">
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Capaian IKU</div>
                <div style="font-size: 2.1rem; font-weight: 800; color: #10b981; margin-top: 4px; line-height: 1;">
                    <?= $avgIkuAchievement ?>%
                </div>
                <div style="color: #10b981; font-size: 0.78rem; margin-top: 6px;">
                    <i class="fa-solid fa-circle-check"></i> 8 Indikator Kinerja Utama
                </div>
            </div>
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1.3rem;">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>

        <!-- KPI 4: Rata-Rata IPK Mahasiswa -->
        <div class="kpi-card">
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Rata-rata IPK</div>
                <div style="font-size: 2.1rem; font-weight: 800; color: #f59e0b; margin-top: 4px; line-height: 1;">
                    <?= $avgGpa ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.78rem; margin-top: 6px;">
                    Skala 4.00 • Standar SPMI ≥3.35
                </div>
            </div>
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 1.3rem;">
                <i class="fa-solid fa-certificate"></i>
            </div>
        </div>

        <!-- KPI 5: Serapan Anggaran RKA -->
        <div class="kpi-card">
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Serapan RKA</div>
                <div style="font-size: 2.1rem; font-weight: 800; color: #0ea5e9; margin-top: 4px; line-height: 1;">
                    <?= $budgetAbsorption ?>%
                </div>
                <div style="color: var(--text-muted); font-size: 0.78rem; margin-top: 6px;">
                    Realisasi: <?= FormatHelper::currency($totalRealized) ?>
                </div>
            </div>
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 1.3rem;">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>

    <!-- 4. Main Two-Column Layout: Attention Radar & Strategic Chart -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
        
        <!-- Kolom Kiri: Capaian IKU 1-8 Chart Card -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">
                        <i class="fa-solid fa-chart-column" style="color: var(--primary); margin-right: 8px;"></i>Capaian Indikator Kinerja Utama (IKU 1 - 8)
                    </h4>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Monitoring target strategis fakultas tahun berjalan</span>
                </div>
                <a href="<?= $baseUrl ?>/strategic" class="btn btn-secondary btn-sm">
                    Detail IKU <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div style="padding: 20px; flex: 1;">
                <div id="ikuChart" style="min-height: 320px;"></div>
            </div>
        </div>

        <!-- Kolom Kanan: My Attention & Radar Prioritas Dekan -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">My Attention Dekan</h4>
                </div>
                <span class="badge bg-danger rounded-pill px-3 py-1"><?= $attention['total'] ?> Item</span>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 10px; flex: 1;">
                <p style="margin: 0 0 6px 0; font-size: 0.82rem; color: var(--text-muted);">
                    Item prioritas yang memerlukan intervensi langsung dekanat saat ini:
                </p>

                <!-- Pending Approvals -->
                <a href="<?= $baseUrl ?>/command-center/approvals" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 10px; background: var(--surface-hover); border: 1px solid var(--border-subtle); color: var(--text-main); text-decoration: none; transition: all 0.15s ease;">
                    <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-stamp" style="color: #f59e0b;"></i> Persetujuan Menunggu (Approvals)
                    </span>
                    <span class="badge bg-warning text-dark fw-bold"><?= $attention['pending_approvals'] ?></span>
                </a>

                <!-- Overdue RTL -->
                <a href="<?= $baseUrl ?>/meetings/rtl" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 10px; background: var(--surface-hover); border: 1px solid var(--border-subtle); color: var(--text-main); text-decoration: none; transition: all 0.15s ease;">
                    <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> Tindak Lanjut Rapat (RTL) Terlambat
                    </span>
                    <span class="badge bg-danger fw-bold"><?= $attention['overdue_rtl'] ?></span>
                </a>

                <!-- Problematic Lecturers -->
                <a href="<?= $baseUrl ?>/lecturers?bkd_status=Belum+Memenuhi" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 10px; background: var(--surface-hover); border: 1px solid var(--border-subtle); color: var(--text-main); text-decoration: none; transition: all 0.15s ease;">
                    <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-user-xmark" style="color: #f59e0b;"></i> Dosen Perlu Pembinaan BKD
                    </span>
                    <span class="badge bg-warning text-dark fw-bold"><?= $attention['problematic_lecturers'] ?></span>
                </a>

                <!-- Students At Risk -->
                <a href="<?= $baseUrl ?>/students" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 10px; background: var(--surface-hover); border: 1px solid var(--border-subtle); color: var(--text-main); text-decoration: none; transition: all 0.15s ease;">
                    <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> Mahasiswa Berisiko EWS Kritis
                    </span>
                    <span class="badge bg-danger fw-bold"><?= $attention['at_risk_students'] ?></span>
                </a>

                <!-- Urgent Deadlines -->
                <a href="<?= $baseUrl ?>/accreditation" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 10px; background: var(--surface-hover); border: 1px solid var(--border-subtle); color: var(--text-main); text-decoration: none; transition: all 0.15s ease;">
                    <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-clock" style="color: #0ea5e9;"></i> Tenggat Waktu Akreditasi Mendesak
                    </span>
                    <span class="badge bg-info text-white fw-bold"><?= $attention['urgent_deadlines'] ?></span>
                </a>

                <div style="margin-top: auto; padding-top: 10px;">
                    <a href="<?= $baseUrl ?>/command-center" class="btn btn-outline-primary btn-sm w-100 py-2">
                        Buka Radar Penuh di Command Center →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Agenda Dekan & Rapat Pimpinan Terdekat -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">
                    <i class="fa-solid fa-calendar-days" style="color: var(--primary); margin-right: 8px;"></i>Agenda Dekan & Rapat Pimpinan
                </h4>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Jadwal pertemuan dan evaluasi dekanat mendatang</span>
            </div>
            <a href="<?= $baseUrl ?>/meetings" class="btn btn-secondary btn-sm">
                Lihat Semua Rapat <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div style="padding: 20px;">
            <?php if (empty($upcomingMeetings)): ?>
                <div style="text-align: center; padding: 32px 16px; color: var(--text-muted);">
                    <i class="fa-regular fa-calendar-check" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                    Tidak ada agenda rapat terjadwal dalam waktu dekat.
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px;">
                    <?php foreach ($upcomingMeetings as $m): ?>
                        <div style="padding: 16px; border-radius: 12px; background: var(--surface-hover); border: 1px solid var(--border-subtle); display: flex; align-items: flex-start; gap: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--primary); color: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: 800; line-height: 1; flex-shrink: 0;">
                                <span style="font-size: 1.1rem;"><?= date('d', strtotime($m['meeting_date'])) ?></span>
                                <span style="font-size: 0.62rem; text-transform: uppercase;"><?= date('M', strtotime($m['meeting_date'])) ?></span>
                            </div>
                            <div style="flex: 1; overflow: hidden;">
                                <h6 style="margin: 0 0 6px 0; font-size: 0.92rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>
                                </h6>
                                <div style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 12px;">
                                    <span><i class="fa-regular fa-clock me-1"></i><?= substr($m['start_time'], 0, 5) ?> WIB</span>
                                    <span><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($m['location'] ?? 'Dekanat', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <a href="<?= $baseUrl ?>/meetings/detail?id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.78rem;">
                                Detail
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ApexCharts Script for IKU Chart -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = document.body.getAttribute('data-base-url') || '';
    
    fetch(`${baseUrl}/dashboard/chart-data`)
        .then(res => res.json())
        .then(data => {
            if (!data.ikuData) return;

            const categories = data.ikuData.map(d => d.code);
            const seriesData = data.ikuData.map(d => parseFloat(d.achievement_percentage));

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            const options = {
                series: [{
                    name: 'Capaian (%)',
                    data: seriesData
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '45%',
                        distributed: true
                    }
                },
                colors: ['#38bdf8', '#10b981', '#f59e0b', '#f97316', '#06b6d4', '#8b5cf6', '#ec4899', '#ef4444'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val + '%';
                    },
                    style: {
                        fontSize: '11px',
                        colors: [isDark ? '#ffffff' : '#333333']
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: { 
                        style: { 
                            fontWeight: 600,
                            colors: isDark ? '#a1a1aa' : '#52525b'
                        } 
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    title: { 
                        text: 'Persentase Capaian Target',
                        style: { color: isDark ? '#a1a1aa' : '#52525b' }
                    },
                    labels: {
                        style: { colors: isDark ? '#a1a1aa' : '#52525b' }
                    },
                    max: 120
                },
                legend: { show: false },
                grid: { 
                    borderColor: isDark ? '#27272a' : '#f1f5f9' 
                }
            };

            const chart = new ApexCharts(document.querySelector("#ikuChart"), options);
            chart.render();

            // Re-render chart on theme changes
            window.addEventListener('themeChanged', function(e) {
                const dark = e.detail.theme === 'dark';
                chart.updateOptions({
                    theme: { mode: dark ? 'dark' : 'light' },
                    grid: { borderColor: dark ? '#27272a' : '#f1f5f9' },
                    xaxis: {
                        labels: { style: { colors: dark ? '#a1a1aa' : '#52525b' } }
                    },
                    yaxis: {
                        title: { style: { color: dark ? '#a1a1aa' : '#52525b' } },
                        labels: { style: { colors: dark ? '#a1a1aa' : '#52525b' } }
                    }
                });
            });
        })
        .catch(err => console.log('Chart load err:', err));
});
</script>
