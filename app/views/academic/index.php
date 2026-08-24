<?php use App\Helpers\FormatHelper; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Kelas Perkuliahan</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $totalClasses ?> <span class="fs-6 fw-normal text-muted">Kelas</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Realisasi Pertemuan</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $realizationRate ?>%</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Rata-rata Presensi Mahasiswa</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $avgAttendance ?>%</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-danger">Kelas Bermasalah</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= $problemClasses ?> <span class="fs-6 fw-normal text-muted">Kelas</span></h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Monitoring Perkuliahan & Presensi</h5>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/academic/courses" class="btn btn-sm btn-outline-secondary btn-crud-sm">
                <i class="ti ti-books"></i> Kurikulum & RPS
            </a>
            <a href="<?= $baseUrl ?>/academic/guidance" class="btn btn-sm btn-outline-primary btn-crud-sm">
                <i class="ti ti-users"></i> Bimbingan
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Dosen Pengampu</th>
                        <th>Prodi</th>
                        <th>Realisasi Tatap Muka</th>
                        <th>Rata-rata Presensi</th>
                        <th class="text-end pe-4">Status & Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $c): ?>
                        <tr class="<?= $c['problem_flag'] ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= $c['course_code'] ?></span>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $c['sks'] ?> SKS)</div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= $c['class_name'] ?></span></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($c['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <strong><?= $c['total_held_meetings'] ?></strong> / <?= $c['total_planned_meetings'] ?> Pertemuan
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-<?= $c['problem_flag'] ? 'danger' : 'success' ?>" style="width: <?= ($c['total_held_meetings'] / ($c['total_planned_meetings'] ?: 1)) * 100 ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <strong><?= $c['average_attendance'] ?>%</strong>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($c['problem_flag']): ?>
                                    <span class="badge bg-danger text-white mb-1"><i class="ti ti-alert-triangle me-1"></i>Bermasalah</span>
                                    <div class="small text-danger" style="max-width: 220px;"><?= htmlspecialchars($c['problem_notes'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
