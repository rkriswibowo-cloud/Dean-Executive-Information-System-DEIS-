<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper;

// Group modules by study program for the top matrix summary
$modulesByProgram = [];
foreach ($modules as $m) {
    $modulesByProgram[$m['program_name']][] = $m;
}
?>

<div class="container-fluid px-0">
    <!-- Breadcrumb & Header Title -->
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Akademik & Mahasiswa</li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Cek Modul Praktikum Lab</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="ti ti-flask text-primary"></i> Cek & Verifikasi Modul Praktikum Laboratorium
                <span class="badge bg-primary-subtle text-primary fs-7 border border-primary-subtle px-2 py-1">Dekan Leadership Suite</span>
            </h3>
            <p class="text-muted small mb-0">Pemantauan ketercapaian modul praktikum lab (contoh: target 12/12 per semester) per program studi dan mekanisme konfirmasi langsung ke Kaprodi.</p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPracticumModal">
                <i class="ti ti-plus"></i> Tambah Matakuliah Lab
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="window.print()">
                <i class="ti ti-printer"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($flash['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="ti ti-circle-check fs-4 flex-shrink-0"></i>
            <div><?= htmlspecialchars($flash['success']) ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash['danger'])): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="ti ti-alert-circle fs-4 flex-shrink-0"></i>
            <div><?= htmlspecialchars($flash['danger']) ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Matakuliah Lab</div>
                        <div class="fs-4 fw-bold text-dark mt-1"><?= (int)$stats['total_labs'] ?> <span class="fs-7 text-muted fw-normal">Matakuliah</span></div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Tersebar di 3 Program Studi</div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-flask-2 fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Ketercapaian Modul Lab</div>
                        <div class="fs-4 fw-bold text-success mt-1">
                            <?= (float)($stats['overall_completion_rate'] ?? 0) ?>%
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                            <?= (int)$stats['sum_completed_modules'] ?> / <?= (int)$stats['sum_target_modules'] ?> Total Modul
                        </div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-chart-bar fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Terpenuhi 100% (12/12)</div>
                        <div class="fs-4 fw-bold text-primary mt-1"><?= (int)$stats['count_100_percent'] ?> <span class="fs-7 text-muted fw-normal">Kelas Lab</span></div>
                        <div class="text-success small mt-1 fw-medium" style="font-size: 0.75rem;"><i class="ti ti-circle-check"></i> Siap Pelaksanaan Praktikum</div>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-checklist fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Dikonfirmasi ke Kaprodi</div>
                        <div class="fs-4 fw-bold <?= (int)$stats['count_confirmed_kaprodi'] > 0 ? 'text-warning' : 'text-dark' ?> mt-1">
                            <?= (int)$stats['count_confirmed_kaprodi'] + (int)$stats['count_attention_needed'] ?> <span class="fs-7 text-muted fw-normal">Tindak Lanjut</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menunggu verifikasi / revisi modul</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-send fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMAT REKAPITULASI KETERCAPAIAN MODUL PER PRODI & SEMESTER (Card Grid Matrix) -->
    <div class="card border shadow-xs rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-layout-grid text-primary"></i> Matriks Capaian Modul Praktikum per Program Studi & Semester
                </h5>
                <span class="text-muted small">Ringkasan pemenuhan target modul lab (contoh: 12/12, 10/12, 14/14) per tingkatan semester</span>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <?php
                // Build aggregated matrix data by program
                $prodiGroups = [];
                foreach ($prodiSummary as $row) {
                    $prodiGroups[$row['program_name']][] = $row;
                }
                ?>

                <?php if (empty($prodiGroups)): ?>
                    <div class="col-12 text-center py-4 text-muted">
                        <i class="ti ti-folder-off fs-1 d-block mb-2 text-muted"></i>
                        Belum ada data modul praktikum yang tercatat.
                    </div>
                <?php else: ?>
                    <?php foreach ($prodiGroups as $pName => $semesters): ?>
                        <div class="col-12 col-lg-4">
                            <div class="card h-100 border shadow-xs rounded-3 bg-body-tertiary">
                                <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-school text-primary fs-5"></i>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($pName) ?></span>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 0.7rem;">
                                        Kaprodi: <?= htmlspecialchars($semesters[0]['kaprodi_name'] ?? 'Kaprodi') ?>
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex flex-column gap-2.5">
                                        <?php foreach ($semesters as $s): ?>
                                            <?php 
                                            $isComplete = ($s['total_completed_modules'] >= $s['total_target_modules']);
                                            $badgeClass = $isComplete ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                                            $progressColor = $isComplete ? 'bg-success' : 'bg-warning';
                                            ?>
                                            <div class="p-2.5 bg-body rounded-2 border">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="d-flex align-items-center gap-1.5">
                                                        <span class="badge bg-secondary-subtle text-secondary border px-1.5 py-0.5" style="font-size: 0.7rem;">
                                                            Semester <?= $s['semester'] ?>
                                                        </span>
                                                        <span class="small text-dark fw-semibold"><?= $s['total_courses'] ?> Matakuliah Lab</span>
                                                    </div>
                                                    <span class="badge <?= $badgeClass ?> border font-monospace fw-bold px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="ti <?= $isComplete ? 'ti-check' : 'ti-clock' ?> me-0.5"></i>
                                                        <?= $s['total_completed_modules'] ?>/<?= $s['total_target_modules'] ?> Modul (<?= $s['achievement_rate'] ?>%)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar <?= $progressColor ?>" role="progressbar" style="width: <?= min(100, $s['achievement_rate']) ?>%;" aria-valuenow="<?= $s['achievement_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTER & DATA TABLE CARD -->
    <div class="card border shadow-xs rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-4">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="ti ti-table text-primary"></i> Detail Matakuliah & Modul Praktikum Lab
                    </h5>
                    <span class="text-muted small">Kelengkapan berkas modul, logbook, rubrik, dan verifikasi Dekan</span>
                </div>
                <div class="col-12 col-md-8">
                    <!-- Filter Form -->
                    <form action="<?= $baseUrl ?>/academic/practicum" method="GET" class="row g-2 justify-content-md-end">
                        <div class="col-auto">
                            <select name="program_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="0">Semua Program Studi</option>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="0">Semua Semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filterSemester == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="Terpenuhi 100%" <?= $filterStatus === 'Terpenuhi 100%' ? 'selected' : '' ?>>Terpenuhi 100%</option>
                                <option value="Dikonfirmasi ke Kaprodi" <?= $filterStatus === 'Dikonfirmasi ke Kaprodi' ? 'selected' : '' ?>>Dikonfirmasi ke Kaprodi</option>
                                <option value="Perlu Perhatian" <?= $filterStatus === 'Perlu Perhatian' ? 'selected' : '' ?>>Perlu Perhatian</option>
                            </select>
                        </div>
                        <?php if ($filterProgram || $filterSemester || $filterStatus): ?>
                            <div class="col-auto">
                                <a href="<?= $baseUrl ?>/academic/practicum" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                    <i class="ti ti-refresh"></i> Reset
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="practicumTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">No</th>
                            <th style="min-width: 140px;">Prodi & Sem.</th>
                            <th style="min-width: 220px;">Matakuliah Lab & Laboratorium</th>
                            <th style="min-width: 180px;">Dosen Pengampu & Aslab</th>
                            <th style="min-width: 160px;">Capaian Modul</th>
                            <th style="min-width: 140px;">Kelengkapan Berkas</th>
                            <th style="min-width: 170px;">Status & Catatan Dekan</th>
                            <th class="text-end pe-3" style="min-width: 160px;">Aksi Dekan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($modules)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-search-off fs-1 d-block mb-2 text-muted"></i>
                                    Tidak ada data modul praktikum yang sesuai dengan filter yang dipilih.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($modules as $m): ?>
                                <?php 
                                $isFull = ($m['completed_modules'] >= $m['target_modules']);
                                $ratioClass = $isFull ? 'text-success' : 'text-warning-emphasis';
                                $badgeStatus = 'bg-success-subtle text-success border-success-subtle';
                                if ($m['status'] === 'Dikonfirmasi ke Kaprodi') {
                                    $badgeStatus = 'bg-primary-subtle text-primary border-primary-subtle';
                                } elseif ($m['status'] === 'Perlu Perhatian' || !$isFull) {
                                    $badgeStatus = 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                                }
                                ?>
                                <tr>
                                    <td class="ps-3 text-muted small"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($m['program_name']) ?></div>
                                        <div class="d-flex align-items-center gap-1 mt-0.5">
                                            <span class="badge bg-secondary-subtle text-secondary border font-monospace px-1.5 py-0.5" style="font-size: 0.68rem;">
                                                Sem. <?= $m['semester'] ?>
                                            </span>
                                            <span class="text-muted small" style="font-size: 0.7rem;"><?= htmlspecialchars($m['program_code']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                                            <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($m['course_code']) ?></span>
                                            <?= htmlspecialchars($m['course_name']) ?>
                                        </div>
                                        <div class="text-muted small mt-0.5" style="font-size: 0.75rem;">
                                            <i class="ti ti-building-warehouse me-0.5 text-info"></i> <?= htmlspecialchars($m['lab_name']) ?>
                                            <span class="badge bg-light text-muted border ms-1"><?= $m['sks_lab'] ?> SKS Lab</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($m['lecturer_name']) ?></div>
                                        <?php if (!empty($m['assistant_name'])): ?>
                                            <div class="text-muted small" style="font-size: 0.72rem;">
                                                <i class="ti ti-user-check me-0.5 text-success"></i> <?= htmlspecialchars($m['assistant_name']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- FORMAT CAPAIAN: 12/12 Modul (100%) -->
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge <?= $isFull ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' ?> border font-monospace fw-bold px-2 py-0.5" style="font-size: 0.8rem;">
                                                <?= $m['completed_modules'] ?>/<?= $m['target_modules'] ?> Modul
                                            </span>
                                            <span class="small font-monospace fw-bold <?= $ratioClass ?>">
                                                <?= $m['completion_percentage'] ?>%
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar <?= $isFull ? 'bg-success' : 'bg-warning' ?>" role="progressbar" style="width: <?= min(100, $m['completion_percentage']) ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1" style="font-size: 0.72rem;">
                                            <span class="text-success d-flex align-items-center gap-1">
                                                <i class="ti ti-file-text text-primary"></i> Modul Lab PDF: <span class="badge bg-light text-dark border">Siap</span>
                                            </span>
                                            <span class="text-muted d-flex align-items-center gap-1">
                                                <i class="ti ti-book text-info"></i> Logbook: 
                                                <span class="badge <?= $m['logbook_status'] === 'Lengkap' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> border">
                                                    <?= $m['logbook_status'] ?>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeStatus ?> border px-2 py-1 mb-1 d-inline-block" style="font-size: 0.75rem;">
                                            <i class="ti <?= $isFull ? 'ti-check' : 'ti-alert-circle' ?> me-0.5"></i> <?= htmlspecialchars($m['status']) ?>
                                        </span>
                                        <?php if (!empty($m['dekan_notes'])): ?>
                                            <div class="text-muted small fst-italic p-1.5 bg-light rounded border mt-1" style="font-size: 0.7rem; max-width: 220px;">
                                                <i class="ti ti-notes text-warning me-0.5"></i> "<?= htmlspecialchars($m['dekan_notes']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <!-- Tombol Konfirmasi ke Kaprodi -->
                                            <button type="button" class="btn btn-xs btn-outline-primary d-flex align-items-center gap-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmModal"
                                                    data-id="<?= $m['id'] ?>"
                                                    data-course="<?= htmlspecialchars($m['course_name']) ?>"
                                                    data-code="<?= htmlspecialchars($m['course_code']) ?>"
                                                    data-prodi="<?= htmlspecialchars($m['program_name']) ?>"
                                                    data-kaprodi="<?= htmlspecialchars($m['kaprodi_name'] ?? 'Kaprodi') ?>"
                                                    data-progress="<?= $m['completed_modules'] ?>/<?= $m['target_modules'] ?>"
                                                    data-notes="<?= htmlspecialchars($m['dekan_notes'] ?? '') ?>"
                                                    title="Kirim Konfirmasi / Disposisi ke Kaprodi">
                                                <i class="ti ti-send"></i> <span class="d-none d-xl-inline">Konfirmasi ke Kaprodi</span>
                                            </button>

                                            <!-- Tombol Edit Progres Modul -->
                                            <button type="button" class="btn btn-xs btn-ghost text-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="<?= $m['id'] ?>"
                                                    data-code="<?= htmlspecialchars($m['course_code']) ?>"
                                                    data-name="<?= htmlspecialchars($m['course_name']) ?>"
                                                    data-lab="<?= htmlspecialchars($m['lab_name']) ?>"
                                                    data-lecturer="<?= htmlspecialchars($m['lecturer_name']) ?>"
                                                    data-assistant="<?= htmlspecialchars($m['assistant_name'] ?? '') ?>"
                                                    data-target="<?= $m['target_modules'] ?>"
                                                    data-completed="<?= $m['completed_modules'] ?>"
                                                    data-logbook="<?= $m['logbook_status'] ?>"
                                                    data-status="<?= $m['status'] ?>"
                                                    data-notes="<?= htmlspecialchars($m['dekan_notes'] ?? '') ?>"
                                                    data-feedback="<?= htmlspecialchars($m['kaprodi_feedback'] ?? '') ?>"
                                                    title="Edit Progres Modul">
                                                <i class="ti ti-edit fs-5"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form action="<?= $baseUrl ?>/academic/practicum/delete" method="POST" class="d-inline" onsubmit="return confirm('Hapus data praktikum <?= addslashes($m['course_name']) ?>?');">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="practicum_id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-ghost text-danger" title="Hapus Data">
                                                    <i class="ti ti-trash fs-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 1: KONFIRMASI KE KAPRODI ================= -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/academic/practicum/confirm" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="practicum_id" id="confirm_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="confirmModalLabel">
                        <i class="ti ti-send"></i> Konfirmasi Modul Lab ke Kaprodi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary" id="confirm_code">TI101</span>
                            <span class="badge bg-warning text-dark" id="confirm_progress">10/12 Modul</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" id="confirm_course">Nama Matakuliah Praktikum</h6>
                        <div class="text-muted small">Program Studi: <strong class="text-dark" id="confirm_prodi">Teknik Informatika</strong></div>
                        <div class="text-muted small">Ditujukan kepada: <strong class="text-primary" id="confirm_kaprodi">Kaprodi</strong></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_notes" class="form-label fw-semibold text-dark small">Catatan & Instruksi Verifikasi Dekan:</label>
                        <textarea name="dekan_notes" id="confirm_notes" class="form-control" rows="4" placeholder="Contoh: Mohon Kaprodi memverifikasi penyelesaian modul 11 & 12 sebelum perkuliahan minggu ke-10..." required></textarea>
                        <div class="form-text small">Catatan ini akan otomatis masuk ke dalam disposisi layanan dan notifikasi pemantauan Kaprodi.</div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="ti ti-send"></i> Kirim Konfirmasi ke Kaprodi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: EDIT PROGRES MODUL PRAKTIKUM ================= -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/academic/practicum/update" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="practicum_id" id="edit_id">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editModalLabel">
                        <i class="ti ti-edit text-primary"></i> Update Ketercapaian Modul Praktikum
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Matakuliah Lab:</label>
                            <input type="text" class="form-control form-control-sm" id="edit_name" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Laboratorium:</label>
                            <input type="text" name="lab_name" id="edit_lab" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Dosen Pengampu:</label>
                            <input type="text" name="lecturer_name" id="edit_lecturer" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Asisten Lab / Laboran:</label>
                            <input type="text" name="assistant_name" id="edit_assistant" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Target Modul:</label>
                            <input type="number" name="target_modules" id="edit_target" class="form-control form-control-sm" min="1" max="30" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Modul Tercapai:</label>
                            <input type="number" name="completed_modules" id="edit_completed" class="form-control form-control-sm" min="0" max="30" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Logbook Praktikan:</label>
                            <select name="logbook_status" id="edit_logbook" class="form-select form-select-sm">
                                <option value="Lengkap">Lengkap</option>
                                <option value="Sebagian">Sebagian</option>
                                <option value="Belum Ada">Belum Ada</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Status Verifikasi:</label>
                            <select name="status" id="edit_status" class="form-select form-select-sm">
                                <option value="Terpenuhi 100%">Terpenuhi 100%</option>
                                <option value="Progres Berjalan">Progres Berjalan</option>
                                <option value="Dikonfirmasi ke Kaprodi">Dikonfirmasi ke Kaprodi</option>
                                <option value="Perlu Perhatian">Perlu Perhatian</option>
                                <option value="Revisi Modul">Revisi Modul</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Catatan Dekan:</label>
                            <textarea name="dekan_notes" id="edit_dekan_notes" class="form-control form-control-sm" rows="2" placeholder="Catatan evaluasi dekanat..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Umpan Balik / Respon Kaprodi:</label>
                            <textarea name="kaprodi_feedback" id="edit_feedback" class="form-control form-control-sm" rows="2" placeholder="Tanggapan tindak lanjut dari Kaprodi..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="ti ti-device-floppy"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: TAMBAH MATAKULIAH PRAKTIKUM BARU ================= -->
<div class="modal fade" id="createPracticumModal" tabindex="-1" aria-labelledby="createPracticumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/academic/practicum/create" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-header border-bottom bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="createPracticumModalLabel">
                        <i class="ti ti-plus"></i> Tambah Matakuliah Praktikum Laboratorium
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Program Studi:</label>
                            <select name="study_program_id" class="form-select form-select-sm" required>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['degree']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Semester:</label>
                            <select name="semester" class="form-select form-select-sm" required>
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>">Semester <?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">Kode Matakuliah:</label>
                            <input type="text" name="course_code" class="form-control form-control-sm" placeholder="Contoh: TI101" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold text-dark small">Nama Matakuliah Praktikum:</label>
                            <input type="text" name="course_name" class="form-control form-control-sm" placeholder="Contoh: Pemrograman Berorientasi Objek Lab" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Laboratorium:</label>
                            <input type="text" name="lab_name" class="form-control form-control-sm" placeholder="Contoh: Lab Rekayasa Perangkat Lunak" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Dosen Pengampu:</label>
                            <input type="text" name="lecturer_name" class="form-control form-control-sm" placeholder="Nama lengkap Dosen" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Asisten Lab / Laboran:</label>
                            <input type="text" name="assistant_name" class="form-control form-control-sm" placeholder="Nama Aslab pendamping">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Target Modul:</label>
                            <input type="number" name="target_modules" class="form-control form-control-sm" value="12" min="1" max="30" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold text-dark small">Modul Selesai Saat Ini:</label>
                            <input type="number" name="completed_modules" class="form-control form-control-sm" value="12" min="0" max="30" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Catatan Dekan Awal (Opsional):</label>
                            <textarea name="dekan_notes" class="form-control form-control-sm" rows="2" placeholder="Catatan atau instruksi pemantauan..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="ti ti-plus"></i> Tambahkan ke Pemantauan Dekan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Script Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Confirm Modal Population
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        confirmModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('confirm_id').value = button.getAttribute('data-id');
            document.getElementById('confirm_code').textContent = button.getAttribute('data-code');
            document.getElementById('confirm_course').textContent = button.getAttribute('data-course');
            document.getElementById('confirm_prodi').textContent = button.getAttribute('data-prodi');
            document.getElementById('confirm_kaprodi').textContent = button.getAttribute('data-kaprodi');
            document.getElementById('confirm_progress').textContent = button.getAttribute('data-progress') + ' Modul';
            
            const existingNotes = button.getAttribute('data-notes');
            document.getElementById('confirm_notes').value = existingNotes || '';
        });
    }

    // 2. Edit Modal Population
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-code') + ' - ' + btn.getAttribute('data-name');
            document.getElementById('edit_lab').value = btn.getAttribute('data-lab');
            document.getElementById('edit_lecturer').value = btn.getAttribute('data-lecturer');
            document.getElementById('edit_assistant').value = btn.getAttribute('data-assistant');
            document.getElementById('edit_target').value = btn.getAttribute('data-target');
            document.getElementById('edit_completed').value = btn.getAttribute('data-completed');
            document.getElementById('edit_logbook').value = btn.getAttribute('data-logbook');
            document.getElementById('edit_status').value = btn.getAttribute('data-status');
            document.getElementById('edit_dekan_notes').value = btn.getAttribute('data-notes');
            document.getElementById('edit_feedback').value = btn.getAttribute('data-feedback');
        });
    }
});
</script>
