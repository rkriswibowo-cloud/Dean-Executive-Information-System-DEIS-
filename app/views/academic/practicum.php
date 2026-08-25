<?php
use App\Helpers\CsrfHelper;
use App\Helpers\FormatHelper;

// Group modules by study program
$modulesByProgram = [];
foreach ($modules as $m) {
    $modulesByProgram[$m['program_name']][] = $m;
}
?>

<div class="container-fluid px-0">
    <!-- Breadcrumb & Header Title -->
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Akademik & Mahasiswa</li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Cek Modul Praktikum Lab</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap">
                <i class="ti ti-flask text-primary"></i> Cek Modul Praktikum & Laboratorium
                <span class="badge bg-primary-subtle text-primary fs-7 border border-primary-subtle px-2 py-1">Dekan Leadership Suite</span>
            </h3>
            <p class="text-muted small mb-0">1 Matakuliah Praktikum wajib memiliki 1 Buku Modul Lab lengkap (contoh: 10 MK Praktikum = 10 Modul, 1 MK = 1 Modul). Hasil verifikasi dapat langsung dikonfirmasikan ke Kaprodi.</p>
        </div>
        <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#createPracticumModal">
                <i class="ti ti-plus"></i> Tambah Matakuliah Lab
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 px-3" onclick="window.print()">
                <i class="ti ti-printer"></i> Cetak Rekap
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

    <!-- KPI SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total MK Praktikum</div>
                        <div class="fs-4 fw-bold text-dark mt-1"><?= (int)$stats['total_labs'] ?> <span class="fs-7 text-muted fw-normal">MK Lab</span></div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Wajib <?= (int)$stats['sum_target_modules'] ?> Modul Lengkap</div>
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
                        <div class="text-muted small fw-medium">Total Modul Terbit</div>
                        <div class="fs-4 fw-bold text-success mt-1">
                            <?= (int)$stats['sum_completed_modules'] ?> / <?= (int)$stats['sum_target_modules'] ?> <span class="fs-7 text-success fw-semibold">(<?= (float)($stats['overall_completion_rate'] ?? 0) ?>%)</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Capaian seluruh fakultas</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-book fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-xs rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Modul Terpenuhi (1/1)</div>
                        <div class="fs-4 fw-bold text-primary mt-1"><?= (int)$stats['count_100_percent'] ?> <span class="fs-7 text-muted fw-normal">MK Siap</span></div>
                        <div class="text-success small mt-1 fw-medium" style="font-size: 0.75rem;"><i class="ti ti-circle-check"></i> Berkas Praktikum Siap</div>
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
                        <div class="text-muted small fw-medium">Perlu Tindak Lanjut</div>
                        <div class="fs-4 fw-bold <?= (int)$stats['count_attention_needed'] > 0 ? 'text-warning' : 'text-dark' ?> mt-1">
                            <?= (int)$stats['count_attention_needed'] ?> <span class="fs-7 text-muted fw-normal">Modul Belum Ada</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">Perlu konfirmasi ke Kaprodi</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-send fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RINGKASAN CAPAIAN MODUL PER PRODI (10 MK = 10 MODUL) -->
    <div class="row g-3 mb-4">
        <?php foreach ($prodiTotals as $pt): ?>
            <?php 
            $ptIsFull = ($pt['total_completed_modules'] >= $pt['total_target_modules'] && $pt['total_target_modules'] > 0);
            $ptBorder = $ptIsFull ? 'border-success-subtle' : 'border-warning-subtle';
            $ptBadgeClass = $ptIsFull ? 'bg-success text-white' : 'bg-warning text-dark';
            $ptProgressClass = $ptIsFull ? 'bg-success' : 'bg-warning';
            ?>
            <div class="col-12 col-md-4">
                <div class="card h-100 border shadow-xs rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary font-monospace fw-bold"><?= htmlspecialchars($pt['program_code']) ?></span>
                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($pt['program_name']) ?></h6>
                            </div>
                            <span class="badge <?= $ptBadgeClass ?> font-monospace fw-bold px-2 py-1" style="font-size: 0.75rem;">
                                <?= $pt['total_completed_modules'] ?>/<?= $pt['total_target_modules'] ?> Modul (<?= $pt['achievement_rate'] ?>%)
                            </span>
                        </div>

                        <div class="text-muted small mb-2" style="font-size: 0.78rem;">
                            <strong><?= $pt['total_courses'] ?> MK Praktikum</strong> ➡️ Wajib <strong><?= $pt['total_target_modules'] ?> Modul Lab</strong>
                            <?php if ($pt['unfulfilled_count'] > 0): ?>
                                <span class="text-warning-emphasis fw-semibold">(<?= $pt['unfulfilled_count'] ?> Belum Terbit)</span>
                            <?php else: ?>
                                <span class="text-success fw-semibold"><i class="ti ti-check"></i> Lengkap 100%</span>
                            <?php endif; ?>
                        </div>

                        <div class="progress mb-2" style="height: 7px;">
                            <div class="progress-bar <?= $ptProgressClass ?>" role="progressbar" style="width: <?= min(100, $pt['achievement_rate']) ?>%;" aria-valuenow="<?= $pt['achievement_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.72rem;">
                            <span>Kaprodi: <strong><?= htmlspecialchars($pt['kaprodi_name'] ?? 'Kaprodi') ?></strong></span>
                            <a href="<?= $baseUrl ?>/academic/practicum?program_id=<?= $pt['program_id'] ?>" class="text-primary text-decoration-none fw-semibold">
                                Filter Prodi <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MATRIKS CAPAIAN PER SEMESTER -->
    <div class="card border shadow-xs rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-layout-grid text-primary"></i> Rincian Capaian Modul per Semester (Contoh: 1 MK = 1/1 Modul)
                </h5>
                <span class="text-muted small">Status pemenuhan modul lab di setiap tingkatan semester (1 Semester 1 MK = 1 Modul, 2 MK = 2 Modul)</span>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <?php
                $prodiGroups = [];
                foreach ($prodiSummary as $row) {
                    $prodiGroups[$row['program_name']][] = $row;
                }
                ?>

                <?php foreach ($prodiGroups as $pName => $semesters): ?>
                    <div class="col-12 col-lg-4">
                        <div class="card h-100 border shadow-xs rounded-3 bg-body-tertiary">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-school text-primary fs-5"></i>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($pName) ?></span>
                                </div>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.7rem;">
                                    <?= count($semesters) ?> Tingkatan
                                </span>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-column gap-2">
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
                                                    <span class="small text-dark fw-semibold"><?= $s['total_courses'] ?> MK Praktikum</span>
                                                </div>
                                                <span class="badge <?= $badgeClass ?> border font-monospace fw-bold px-2 py-0.5" style="font-size: 0.75rem;">
                                                    <i class="ti <?= $isComplete ? 'ti-check' : 'ti-clock' ?> me-0.5"></i>
                                                    <?= $s['total_completed_modules'] ?>/<?= $s['total_target_modules'] ?> Modul (<?= $s['achievement_rate'] ?>%)
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar <?= $progressColor ?>" role="progressbar" style="width: <?= min(100, $s['achievement_rate']) ?>%;"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- FILTER & DATA TABLE CARD -->
    <div class="card border shadow-xs rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center g-2">
                <div class="col-12 col-lg-5">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="ti ti-table text-primary"></i> Daftar Matakuliah Praktikum & Dokumen Modul
                    </h5>
                    <span class="text-muted small">Setiap 1 MK Praktikum memiliki 1 Dokumen Modul Lab & Berkas Terkait</span>
                </div>
                <div class="col-12 col-lg-7">
                    <!-- Responsive Filter Form -->
                    <form action="<?= $baseUrl ?>/academic/practicum" method="GET" class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <select name="program_id" class="form-select form-select-sm" style="width: auto; min-width: 170px;" onchange="this.form.submit()">
                            <option value="0">Semua Program Studi</option>
                            <?php foreach ($programs as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="semester" class="form-select form-select-sm" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
                            <option value="0">Semua Semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>" <?= $filterSemester == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="status" class="form-select form-select-sm" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Terpenuhi" <?= $filterStatus === 'Terpenuhi' ? 'selected' : '' ?>>Terpenuhi (1/1)</option>
                            <option value="Dikonfirmasi ke Kaprodi" <?= $filterStatus === 'Dikonfirmasi ke Kaprodi' ? 'selected' : '' ?>>Dikonfirmasi ke Kaprodi</option>
                            <option value="Belum Lengkap" <?= $filterStatus === 'Belum Lengkap' ? 'selected' : '' ?>>Belum Lengkap (0/1)</option>
                        </select>
                        <?php if ($filterProgram || $filterSemester || $filterStatus): ?>
                            <a href="<?= $baseUrl ?>/academic/practicum" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
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
                            <th class="ps-3 text-center" style="width: 45px;">No</th>
                            <th style="min-width: 140px;">Prodi & Sem.</th>
                            <th style="min-width: 220px;">Matakuliah Lab & Laboratorium</th>
                            <th style="min-width: 180px;">Dosen Pengampu & Aslab</th>
                            <th style="min-width: 150px;" class="text-center">Status Modul Lab</th>
                            <th style="min-width: 160px;">Dokumen Berkas</th>
                            <th style="min-width: 160px;">Status & Catatan Dekan</th>
                            <th class="text-end pe-3" style="min-width: 170px;">Aksi Dekan</th>
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
                                $isReady = ((int)$m['is_module_ready'] === 1);
                                $badgeStatus = $isReady ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                                if ($m['status'] === 'Dikonfirmasi ke Kaprodi') {
                                    $badgeStatus = 'bg-primary-subtle text-primary border-primary-subtle';
                                }
                                ?>
                                <tr>
                                    <td class="ps-3 text-center text-muted small"><?= $no++ ?></td>
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
                                            <span class="badge bg-light text-muted border ms-1"><?= $m['sks_lab'] ?> SKS</span>
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
                                    <td class="text-center">
                                        <!-- FORMAT 1 MK = 1 MODUL (1/1 atau 0/1) -->
                                        <?php if ($isReady): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1" style="font-size: 0.8rem;">
                                                <i class="ti ti-check me-0.5"></i> 1/1 Modul (100%)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace fw-bold px-2 py-1" style="font-size: 0.8rem;">
                                                <i class="ti ti-clock me-0.5"></i> 0/1 Modul (0%)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isReady && !empty($m['module_file'])): ?>
                                            <div class="small text-success d-flex align-items-center gap-1">
                                                <i class="ti ti-file-text text-primary"></i>
                                                <span class="text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($m['module_file']) ?>">
                                                    <?= htmlspecialchars($m['module_file']) ?>
                                                </span>
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.7rem;">
                                                <i class="ti ti-book text-info"></i> Logbook: <span class="badge bg-light text-dark border"><?= $m['logbook_status'] ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-danger border">
                                                <i class="ti ti-alert-triangle me-0.5"></i> Belum Ada File
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeStatus ?> border px-2 py-0.5 mb-1 d-inline-block" style="font-size: 0.75rem;">
                                            <i class="ti <?= $isReady ? 'ti-check' : 'ti-alert-circle' ?> me-0.5"></i> <?= htmlspecialchars($m['status']) ?>
                                        </span>
                                        <?php if (!empty($m['dekan_notes'])): ?>
                                            <div class="text-muted small fst-italic p-1 bg-light rounded border mt-0.5" style="font-size: 0.7rem; max-width: 200px;">
                                                "<?= htmlspecialchars($m['dekan_notes']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <!-- RESPONSIVE ACTION BUTTONS GROUP -->
                                        <div class="d-inline-flex flex-wrap align-items-center justify-content-end gap-1">
                                            <!-- Button Konfirmasi ke Kaprodi -->
                                            <button type="button" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 px-2 py-1 shadow-xs" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmModal"
                                                    data-id="<?= $m['id'] ?>"
                                                    data-course="<?= htmlspecialchars($m['course_name']) ?>"
                                                    data-code="<?= htmlspecialchars($m['course_code']) ?>"
                                                    data-prodi="<?= htmlspecialchars($m['program_name']) ?>"
                                                    data-kaprodi="<?= htmlspecialchars($m['kaprodi_name'] ?? 'Kaprodi') ?>"
                                                    data-ready="<?= $m['is_module_ready'] ?>"
                                                    data-notes="<?= htmlspecialchars($m['dekan_notes'] ?? '') ?>"
                                                    title="Konfirmasi / Kirim Disposisi ke Kaprodi">
                                                <i class="ti ti-send"></i>
                                                <span class="d-none d-xxl-inline">Konfirmasi</span>
                                            </button>

                                            <!-- Button Edit Modul -->
                                            <button type="button" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1 px-2 py-1 shadow-xs"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="<?= $m['id'] ?>"
                                                    data-code="<?= htmlspecialchars($m['course_code']) ?>"
                                                    data-name="<?= htmlspecialchars($m['course_name']) ?>"
                                                    data-lab="<?= htmlspecialchars($m['lab_name']) ?>"
                                                    data-lecturer="<?= htmlspecialchars($m['lecturer_name']) ?>"
                                                    data-assistant="<?= htmlspecialchars($m['assistant_name'] ?? '') ?>"
                                                    data-ready="<?= $m['is_module_ready'] ?>"
                                                    data-file="<?= htmlspecialchars($m['module_file'] ?? '') ?>"
                                                    data-logbook="<?= $m['logbook_status'] ?>"
                                                    data-status="<?= $m['status'] ?>"
                                                    data-notes="<?= htmlspecialchars($m['dekan_notes'] ?? '') ?>"
                                                    data-feedback="<?= htmlspecialchars($m['kaprodi_feedback'] ?? '') ?>"
                                                    title="Edit Status Modul">
                                                <i class="ti ti-edit"></i>
                                                <span class="d-none d-xxl-inline">Edit</span>
                                            </button>

                                            <!-- Button Hapus -->
                                            <form action="<?= $baseUrl ?>/academic/practicum/delete" method="POST" class="d-inline" onsubmit="return confirm('Hapus data praktikum <?= addslashes($m['course_name']) ?>?');">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="practicum_id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger d-inline-flex align-items-center p-1" title="Hapus Data">
                                                    <i class="ti ti-trash"></i>
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
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= $baseUrl ?>/academic/practicum/confirm" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="practicum_id" id="confirm_id">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="confirmModalLabel">
                        <i class="ti ti-send"></i> Konfirmasi Modul Lab ke Kaprodi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary font-monospace" id="confirm_code">TI101</span>
                            <span class="badge" id="confirm_status_badge">1/1 Modul</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" id="confirm_course">Nama Matakuliah Praktikum</h6>
                        <div class="text-muted small">Program Studi: <strong class="text-dark" id="confirm_prodi">Teknik Informatika</strong></div>
                        <div class="text-muted small">Ditujukan kepada: <strong class="text-primary" id="confirm_kaprodi">Kaprodi</strong></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_notes" class="form-label fw-semibold text-dark small">Catatan & Instruksi Verifikasi Dekan:</label>
                        <textarea name="dekan_notes" id="confirm_notes" class="form-control" rows="4" placeholder="Contoh: Mohon Kaprodi segera memverifikasi kelengkapan modul praktikum sebelum perkuliahan minggu ke-2..." required></textarea>
                        <div class="form-text small">Catatan ini akan otomatis masuk ke dalam disposisi dan notifikasi Kaprodi yang bersangkutan.</div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3">
                        <i class="ti ti-send"></i> Kirim Konfirmasi ke Kaprodi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: EDIT STATUS & DOKUMEN MODUL ================= -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= $baseUrl ?>/academic/practicum/update" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="practicum_id" id="edit_id">

                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editModalLabel">
                        <i class="ti ti-edit text-primary"></i> Update Status Modul Praktikum
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
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">Ketersediaan Dokumen Modul:</label>
                            <select name="is_module_ready" id="edit_ready" class="form-select form-select-sm" required>
                                <option value="1">Tersedia & Lengkap (1/1 Modul)</option>
                                <option value="0">Belum Ada / Draft (0/1 Modul)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">Nama File Modul (PDF/DOC):</label>
                            <input type="text" name="module_file" id="edit_file" class="form-control form-control-sm" placeholder="Contoh: Modul_PBO_2026.pdf">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">Status Verifikasi:</label>
                            <select name="status" id="edit_status" class="form-select form-select-sm">
                                <option value="Terpenuhi">Terpenuhi</option>
                                <option value="Belum Lengkap">Belum Lengkap</option>
                                <option value="Dikonfirmasi ke Kaprodi">Dikonfirmasi ke Kaprodi</option>
                                <option value="Revisi Modul">Revisi Modul</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Catatan Evaluasi Dekan:</label>
                            <textarea name="dekan_notes" id="edit_dekan_notes" class="form-control form-control-sm" rows="2" placeholder="Catatan evaluasi atau arahan dekanat..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Umpan Balik / Respon Kaprodi:</label>
                            <textarea name="kaprodi_feedback" id="edit_feedback" class="form-control form-control-sm" rows="2" placeholder="Tanggapan tindak lanjut dari Kaprodi..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3">
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
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= $baseUrl ?>/academic/practicum/create" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-header border-bottom bg-primary text-white py-3">
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
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">Ketersediaan Dokumen Modul:</label>
                            <select name="is_module_ready" class="form-select form-select-sm" required>
                                <option value="1">Tersedia & Lengkap (1/1 Modul)</option>
                                <option value="0">Belum Ada / Draft (0/1 Modul)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Catatan Dekan Awal (Opsional):</label>
                            <textarea name="dekan_notes" class="form-control form-control-sm" rows="2" placeholder="Catatan instruksi atau arahan pemantauan dekan..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3">
                        <i class="ti ti-plus"></i> Tambahkan Matakuliah Lab
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
            const isReady = button.getAttribute('data-ready') === '1';

            document.getElementById('confirm_id').value = button.getAttribute('data-id');
            document.getElementById('confirm_code').textContent = button.getAttribute('data-code');
            document.getElementById('confirm_course').textContent = button.getAttribute('data-course');
            document.getElementById('confirm_prodi').textContent = button.getAttribute('data-prodi');
            document.getElementById('confirm_kaprodi').textContent = button.getAttribute('data-kaprodi');
            
            const badge = document.getElementById('confirm_status_badge');
            if (isReady) {
                badge.textContent = '1/1 Modul (Tersedia)';
                badge.className = 'badge bg-success';
            } else {
                badge.textContent = '0/1 Modul (Belum Terbit)';
                badge.className = 'badge bg-warning text-dark';
            }
            
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
            document.getElementById('edit_ready').value = btn.getAttribute('data-ready');
            document.getElementById('edit_file').value = btn.getAttribute('data-file');
            document.getElementById('edit_status').value = btn.getAttribute('data-status');
            document.getElementById('edit_dekan_notes').value = btn.getAttribute('data-notes');
            document.getElementById('edit_feedback').value = btn.getAttribute('data-feedback');
        });
    }
});
</script>
