<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Kelas Perkuliahan</div>
                <div class="kpi-value"><?= number_format($totalClasses) ?></div>
                <div class="text-muted small mt-1">Kelas Aktif Semester Ini</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="fa-solid fa-chalkboard"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Realisasi Pertemuan</div>
                <div class="kpi-value"><?= $realizationRate ?>%</div>
                <div class="text-muted small mt-1">Target 16 Pertemuan</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Rata-rata Presensi</div>
                <div class="kpi-value"><?= $avgAttendance ?>%</div>
                <div class="text-muted small mt-1">Kehadiran Mahasiswa</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(var(--primary-rgb), 0.12); color: var(--primary);">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title text-danger">Kelas Bermasalah</div>
                <div class="kpi-value text-danger"><?= $problemClasses ?></div>
                <div class="text-muted small mt-1">Realisasi < 50% / Presensi Rendah</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0">Monitoring Perkuliahan & Presensi</h5>
            <div class="card-subtitle">Pengawasan langsung perkuliahan, jurnal mengajar, dan tingkat kehadiran kelas</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/academic/courses" class="btn btn-sm btn-outline-secondary btn-crud-sm">
                <i class="fa-solid fa-book"></i> Kurikulum & RPS
            </a>
            <a href="<?= $baseUrl ?>/academic/guidance" class="btn btn-sm btn-soft-primary btn-crud-sm">
                <i class="fa-solid fa-users-rectangle"></i> Bimbingan
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Dosen Pengampu</th>
                        <th>Prodi</th>
                        <th>Realisasi Tatap Muka</th>
                        <th>Rata-rata Presensi</th>
                        <th>Status & Catatan</th>
                        <th class="text-end pe-4">Aksi Dekanat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $c): ?>
                        <tr class="<?= $c['problem_flag'] ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border mb-1"><?= $c['course_code'] ?></span>
                                <div class="fw-bold"><?= htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $c['sks'] ?> SKS)</div>
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
                            <td>
                                <?php if ($c['problem_flag']): ?>
                                    <span class="badge bg-danger text-white mb-1"><i class="ti ti-alert-triangle me-1"></i>Bermasalah</span>
                                    <div class="small text-danger" style="max-width: 220px;"><?= htmlspecialchars($c['problem_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm <?= $c['problem_flag'] ? 'btn-danger' : 'btn-outline-primary' ?> btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalClassAction<?= $c['id'] ?>">
                                    <i class="ti ti-shield-check me-1"></i> Aksi Dekan
                                </button>

                                <!-- Modal Aksi Kelas -->
                                <div class="modal fade" id="modalClassAction<?= $c['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-gavel text-primary"></i> Aksi Evaluasi & Disposisi Dekanat
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/academic/action-class" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8') ?> - Kelas <?= $c['class_name'] ?></div>
                                                        <div class="small text-muted">Dosen: <?= htmlspecialchars($c['lecturer_name'], ENT_QUOTES, 'UTF-8') ?> | Prodi: <?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Tindakan Dekan</label>
                                                        <select name="action_type" class="form-select" required>
                                                            <option value="disposition" <?= $c['problem_flag'] ? 'selected' : '' ?>>⚠️ Terbitkan Instruksi / Disposisi ke Kaprodi & Dosen</option>
                                                            <option value="resolve" <?= !$c['problem_flag'] ? 'selected' : '' ?>>✅ Selesaikan & Tandai Normal</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan Instruksi / Evaluasi Dekan</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Menginstruksikan Kaprodi untuk menegur dosen terkait realisasi perkuliahan yang belum mencapai target..."><?= htmlspecialchars($c['problem_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                        <i class="ti ti-device-floppy me-1"></i> Simpan & Terbitkan Aksi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
