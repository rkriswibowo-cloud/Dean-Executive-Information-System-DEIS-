<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;

$totalCourses = (int)($summary['total_courses'] ?? count($courses));
$completeCount = (int)($summary['complete_count'] ?? 0);
$revisionCount = (int)($summary['revision_count'] ?? 0);
$incompleteCount = (int)($summary['incomplete_count'] ?? 0);
$caseMethodCount = (int)($summary['case_method_count'] ?? 0);

$completePct = $totalCourses > 0 ? round(($completeCount / $totalCourses) * 100, 1) : 0;
$caseMethodPct = $totalCourses > 0 ? round(($caseMethodCount / $totalCourses) * 100, 1) : 0;
$pendingTotal = $revisionCount + $incompleteCount;
$pendingPct = $totalCourses > 0 ? round(($pendingTotal / $totalCourses) * 100, 1) : 0;
$hasActiveFilter = !empty($filterProgram) || !empty($filterStatus) || $filterCaseMethod !== '';
?>

<!-- Header & Navigasi Eksekutif -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 text-white banner-executive-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="badge bg-primary text-white fw-bold mb-2 px-3 py-1 shadow-sm">
                        <i class="ti ti-shield-check me-1"></i> Penjaminan Mutu Pembelajaran & IKU-7
                    </div>
                    <h3 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">Monitoring Kesiapan RPS & Kurikulum OBE</h3>
                    <div class="small" style="color: rgba(255, 255, 255, 0.85) !important;">
                        Pusat kendali audit dekanat untuk keselarasan Capaian Pembelajaran Lulusan (CPL), dokumen RPS, dan metode pembelajaran partisipatif (Case Method / Project-Based).
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if (!empty($obeAvailable)): ?>
                        <a href="<?= $baseUrl ?>/academic/obe-live" class="btn btn-light btn-sm fw-semibold text-primary shadow-sm">
                            <i class="ti ti-database-search me-1"></i> Buka Data Realtime OBE
                        </a>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/academic/export-rps?format=csv" class="btn btn-outline-light btn-sm">
                        <i class="ti ti-download me-1"></i> Ekspor CSV
                    </a>
                    <a href="<?= $baseUrl ?>/academic/export-rps?format=print" target="_blank" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                        <i class="ti ti-printer me-1"></i> Cetak Dokumen Audit PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards RPS (Seragam, Rapi & Presisi) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Mata Kuliah -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card flex-column h-100 p-3 p-md-4">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3 w-100">
                <div style="flex: 1; min-width: 0;">
                    <div class="kpi-title mb-1">Total Mata Kuliah</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="kpi-value"><?= number_format($totalCourses) ?></span>
                        <span class="badge bg-secondary-subtle text-secondary fs-7">MK</span>
                    </div>
                </div>
                <div class="kpi-icon-wrap flex-shrink-0" style="background: rgba(var(--primary-rgb), 0.12); color: var(--primary);">
                    <i class="ti ti-books fs-3"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-top border-opacity-10 w-100">
                <div class="d-flex align-items-center gap-1 text-muted small" style="font-size: 0.8rem;">
                    <i class="ti ti-database text-primary"></i>
                    <span>Master Kurikulum OBE Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: RPS Sah / Lengkap -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card flex-column h-100 p-3 p-md-4">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3 w-100">
                <div style="flex: 1; min-width: 0;">
                    <div class="kpi-title mb-1">RPS Sah / Lengkap</div>
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="kpi-value text-success"><?= $completeCount ?></span>
                        <span class="badge bg-success-subtle text-success fs-7"><?= $completePct ?>%</span>
                    </div>
                </div>
                <div class="kpi-icon-wrap flex-shrink-0" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="ti ti-circle-check fs-3"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-top border-opacity-10 w-100">
                <div class="progress mb-1" style="height: 6px; background: rgba(16, 185, 129, 0.15); border-radius: 99px;">
                    <div class="progress-bar bg-success rounded-pill" style="width: <?= $completePct ?>%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 0.78rem;">
                    <span>Standar Dekanat: ≥80%</span>
                    <span class="fw-semibold text-<?= $completePct >= 80 ? 'success' : 'warning' ?>">
                        <?= $completePct >= 80 ? 'Memenuhi Standar' : 'Perlu Dorongan' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Perlu Revisi / Belum Lengkap -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card flex-column h-100 p-3 p-md-4">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3 w-100">
                <div style="flex: 1; min-width: 0;">
                    <div class="kpi-title mb-1">Perlu Revisi / Belum</div>
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="kpi-value text-<?= $pendingTotal > 0 ? 'danger' : 'muted' ?>">
                            <?= $pendingTotal ?>
                        </span>
                        <span class="badge bg-<?= $pendingTotal > 0 ? 'danger' : 'secondary' ?>-subtle text-<?= $pendingTotal > 0 ? 'danger' : 'secondary' ?> fs-7">
                            <?= $pendingPct ?>%
                        </span>
                    </div>
                </div>
                <div class="kpi-icon-wrap flex-shrink-0" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                    <i class="ti ti-alert-triangle fs-3"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-top border-opacity-10 w-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2" style="font-size: 0.76rem;">
                        <i class="ti ti-edit me-1"></i>Revisi: <?= $revisionCount ?>
                    </span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2" style="font-size: 0.76rem;">
                        <i class="ti ti-x me-1"></i>Belum: <?= $incompleteCount ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Capaian IKU-7 (Case Method) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card flex-column h-100 p-3 p-md-4">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3 w-100">
                <div style="flex: 1; min-width: 0;">
                    <div class="kpi-title mb-1">Capaian IKU-7 (Case Method)</div>
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="kpi-value text-primary"><?= $caseMethodCount ?></span>
                        <span class="badge bg-primary-subtle text-primary fs-7"><?= $caseMethodPct ?>%</span>
                    </div>
                </div>
                <div class="kpi-icon-wrap flex-shrink-0" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                    <i class="ti ti-users-group fs-3"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-top border-opacity-10 w-100">
                <div class="progress mb-1" style="height: 6px; background: rgba(59, 130, 246, 0.15); border-radius: 99px;">
                    <div class="progress-bar bg-primary rounded-pill" style="width: <?= min(100, $caseMethodPct) ?>%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 0.78rem;">
                    <span>Target Dikti: Min. 50%</span>
                    <span class="fw-semibold text-<?= $caseMethodPct >= 50 ? 'success' : 'primary' ?>">
                        <?= $caseMethodPct >= 50 ? 'Target Terpenuhi' : 'Kurang ' . max(0, round(50 - $caseMethodPct, 1)) . '%' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Komparasi Kesiapan RPS per Program Studi -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-chart-bar text-primary fs-5"></i>
                    <h6 class="card-title fw-bold mb-0">Tingkat Kesiapan RPS & Kepatuhan CPL per Program Studi (Master OBE)</h6>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <?= count($prodiStats) ?> Program Studi Terdaftar
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Program Studi</th>
                                <th width="120" class="text-center">Total MK</th>
                                <th width="150">RPS Lengkap</th>
                                <th width="130">Revisi</th>
                                <th width="130">Belum Lengkap</th>
                                <th width="170">Capaian IKU-7 (Case)</th>
                                <th width="200" class="pe-4">Progres Kesiapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prodiStats as $ps): 
                                $pTotal = (int)$ps['total_courses'];
                                $pComp = (int)$ps['complete_count'];
                                $pRate = $pTotal > 0 ? round(($pComp / $pTotal) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-body">
                                        <?= htmlspecialchars($ps['program_name'], ENT_QUOTES, 'UTF-8') ?>
                                        <span class="badge bg-secondary-subtle text-secondary ms-1"><?= $ps['degree'] ?></span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= $pTotal ?></td>
                                    <td><span class="badge bg-success-subtle text-success"><?= $ps['complete_count'] ?> MK</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning"><?= $ps['revision_count'] ?> MK</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger"><?= $ps['incomplete_count'] ?> MK</span></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                             <i class="ti ti-check me-1"></i><?= $ps['case_method_count'] ?> MK
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; background: rgba(125, 125, 125, 0.15); border-radius: 99px;">
                                                <div class="progress-bar bg-<?= $pRate >= 80 ? 'success' : ($pRate >= 50 ? 'warning' : 'danger') ?> rounded-pill" style="width: <?= $pRate ?>%"></div>
                                            </div>
                                            <span class="small fw-bold text-body" style="min-width: 44px; text-align: right;"><?= $pRate ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Tabel Rinci Mata Kuliah & Dokumen RPS -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-list-check text-primary fs-5"></i>
            <h5 class="card-title fw-bold mb-0">Daftar Mata Kuliah, Dokumen RPS & Validasi Dekanat</h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><?= count($courses) ?> MK Sesuai Master OBE</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search text-muted"></i></span>
                <input type="text" id="searchCourseInput" class="form-control border-start-0" placeholder="Cari kode / nama MK..." onkeyup="filterCoursesTable(this.value)">
            </div>
            <select class="form-select form-select-sm" style="width: auto;" onchange="filterRps(this.value, 'program_id')">
                <option value="">Semua Program Studi</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm" style="width: auto;" onchange="filterRps(this.value, 'status')">
                <option value="">Semua Status RPS</option>
                <option value="Lengkap" <?= $filterStatus === 'Lengkap' ? 'selected' : '' ?>>Lengkap</option>
                <option value="Revisi" <?= $filterStatus === 'Revisi' ? 'selected' : '' ?>>Revisi</option>
                <option value="Belum Lengkap" <?= $filterStatus === 'Belum Lengkap' ? 'selected' : '' ?>>Belum Lengkap</option>
            </select>
            <select class="form-select form-select-sm" style="width: auto;" onchange="filterRps(this.value, 'case_method')">
                <option value="">Semua Metode</option>
                <option value="1" <?= $filterCaseMethod === '1' ? 'selected' : '' ?>>Hanya Case Method (IKU-7)</option>
                <option value="0" <?= $filterCaseMethod === '0' ? 'selected' : '' ?>>Reguler Non-Case Method</option>
            </select>
            <?php if ($hasActiveFilter): ?>
                <a href="<?= $baseUrl ?>/academic/rps-monitoring" class="btn btn-sm btn-outline-secondary" title="Reset Semua Filter">
                    <i class="ti ti-rotate me-1"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode & Mata Kuliah</th>
                        <th>Semester / SKS</th>
                        <th>Dosen Koordinator</th>
                        <th>Status RPS</th>
                        <th>Kesesuaian CPL</th>
                        <th>Metode IKU-7</th>
                        <th>Dokumen RPS</th>
                        <th class="text-end pe-4">Aksi Pengesahan</th>
                    </tr>
                </thead>
                <tbody id="coursesTableBody">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada data mata kuliah yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <tr class="course-row">
                                <td class="ps-4">
                                    <span class="badge bg-secondary-subtle text-body border border-secondary-subtle font-monospace mb-1"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <div class="fw-bold text-body"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?> &bull; Kurikulum <?= $c['kurikulum_year'] ?></div>
                                </td>
                                <td>
                                    <div>Semester <?= $c['semester'] ?></div>
                                    <div class="badge bg-secondary-subtle text-secondary"><?= $c['sks'] ?> SKS</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-body"><?= htmlspecialchars($c['lecturer_name'] ?? 'Belum Ditugaskan', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= FormatHelper::statusBadge($c['rps_status']) ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <?= htmlspecialchars($c['cpl_status'] ?? 'Terpenuhi', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($c['case_method_flag'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="ti ti-check me-1"></i>Case Method
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Reguler</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($c['rps_document_url'])): ?>
                                        <a href="<?= htmlspecialchars($c['rps_document_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-crud-sm shadow-sm">
                                            <i class="ti ti-file-text me-1"></i> Buka RPS
                                        </a>
                                        <?php if (!empty($c['rps_updated_at'])): ?>
                                            <div class="text-muted small mt-1" style="font-size: 7.5pt;"><?= date('d/m/Y', strtotime($c['rps_updated_at'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger"><i class="ti ti-x me-1"></i>Belum Unggah</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAuditRps<?= $c['id'] ?>">
                                        <i class="ti ti-certificate me-1"></i> Validasi Dekanat
                                    </button>

                                    <!-- Modal Validasi RPS Dekanat -->
                                    <div class="modal fade" id="modalAuditRps<?= $c['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-bottom py-3 px-4">
                                                    <h6 class="modal-title fw-bold text-body d-flex align-items-center gap-2 mb-0">
                                                        <i class="ti ti-certificate text-primary"></i> Pengesahan & Audit Dokumen RPS Dekanat
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="<?= $baseUrl ?>/academic/action-rps" method="POST">
                                                    <?= CsrfHelper::tokenField() ?>
                                                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="return_url" value="academic/rps-monitoring">
                                                    <div class="modal-body p-4">
                                                        <div class="bg-body-tertiary p-3 rounded-3 mb-3 border">
                                                            <div class="fw-bold text-body"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?> (<?= $c['sks'] ?> SKS)</div>
                                                            <div class="small text-muted">Koordinator: <?= htmlspecialchars($c['lecturer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?> &bull; Prodi: <?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small text-muted">Keputusan Status Pengesahan RPS</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="Lengkap" <?= $c['rps_status'] === 'Lengkap' ? 'selected' : '' ?>>✅ Sahkan & Setujui (Lengkap)</option>
                                                                    <option value="Revisi" <?= $c['rps_status'] === 'Revisi' ? 'selected' : '' ?>>⚠️ Minta Perbaikan / Revisi RPS</option>
                                                                    <option value="Belum Lengkap" <?= $c['rps_status'] === 'Belum Lengkap' ? 'selected' : '' ?>>❌ Belum Lengkap / Dokumen Kurang</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small text-muted">Kesesuaian CPL Kurikulum OBE</label>
                                                                <select name="cpl_status" class="form-select">
                                                                    <option value="Memenuhi Standar OBE" <?= ($c['cpl_status'] ?? '') === 'Memenuhi Standar OBE' ? 'selected' : '' ?>>Memenuhi Standar OBE</option>
                                                                    <option value="Revisi Rubrik Penilaian" <?= ($c['cpl_status'] ?? '') === 'Revisi Rubrik Penilaian' ? 'selected' : '' ?>>Revisi Rubrik Penilaian</option>
                                                                    <option value="Perbaikan Pemetaan CPL" <?= ($c['cpl_status'] ?? '') === 'Perbaikan Pemetaan CPL' ? 'selected' : '' ?>>Perbaikan Pemetaan CPL</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small text-muted">Klasifikasi IKU-7 (Metode Pembelajaran)</label>
                                                                <select name="case_method_flag" class="form-select">
                                                                    <option value="1" <?= !empty($c['case_method_flag']) ? 'selected' : '' ?>>Ya - Case Method / Project-Based (IKU-7)</option>
                                                                    <option value="0" <?= empty($c['case_method_flag']) ? 'selected' : '' ?>>Reguler Non-Case Method</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small text-muted">Link / URL Berkas Dokumen RPS</label>
                                                                <input type="url" name="rps_document_url" class="form-control" value="<?= htmlspecialchars($c['rps_document_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="https://drive.google.com/...">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Catatan Evaluasi / Rekomendasi Dekanat</label>
                                                            <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan arahan penyesuaian materi atau rubrik evaluasi..."><?= htmlspecialchars($c['rps_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top py-2 px-4">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                            <i class="ti ti-check me-1"></i> Simpan Hasil Validasi
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
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

<script>
function filterRps(val, param) {
    const url = new URL(window.location.href);
    if (val) {
        url.searchParams.set(param, val);
    } else {
        url.searchParams.delete(param);
    }
    window.location.href = url.toString();
}

function filterCoursesTable(val) {
    const term = (val || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#coursesTableBody tr.course-row');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(term) ? '' : 'none';
    });
}
</script>

