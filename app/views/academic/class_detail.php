<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;

$heldCount = (int)($summary['held_count'] ?? 0);
$plannedCount = (int)($class['total_planned_meetings'] ?? 16);
$realizationPct = $plannedCount > 0 ? round(($heldCount / $plannedCount) * 100, 1) : 0;
$avgAttendance = round((float)($summary['avg_attendance'] ?? $class['average_attendance']), 1);
?>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white banner-executive-card position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%) !important;">
            <div class="position-relative z-1 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <a href="<?= $baseUrl ?>/academic" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 mb-3 px-3 shadow-none">
                        <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar Kelas
                    </a>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-white text-primary fw-bold px-3 py-1 fs-6"><?= htmlspecialchars($class['course_code'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge bg-light bg-opacity-25 text-white border border-white border-opacity-25">Kelas <?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge bg-light bg-opacity-25 text-white border border-white border-opacity-25"><?= $class['sks'] ?> SKS</span>
                        <?php if ($class['problem_flag']): ?>
                            <span class="badge bg-danger text-white"><i class="ti ti-alert-triangle me-1"></i>Perlu Evaluasi Dekanat</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="fw-bold mb-1 text-white" style="color: #ffffff !important;"><?= htmlspecialchars($class['course_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="small" style="color: rgba(255, 255, 255, 0.85) !important;">
                        <i class="ti ti-user me-1"></i> Dosen: <strong class="text-white"><?= htmlspecialchars($class['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></strong> (NIDN: <?= htmlspecialchars($class['lecturer_nidn'] ?? '-', ENT_QUOTES, 'UTF-8') ?>) &bull;
                        <i class="ti ti-school me-1 ms-2"></i> Prodi: <strong class="text-white"><?= htmlspecialchars($class['program_name'], ENT_QUOTES, 'UTF-8') ?></strong> &bull;
                        <i class="ti ti-door me-1 ms-2"></i> Ruang: <strong class="text-white"><?= htmlspecialchars($class['room'] ?? 'Lab/Kelas', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-light fw-semibold text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMeeting">
                        <i class="ti ti-plus me-1"></i> Catat Pertemuan Baru
                    </button>
                    <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalDekanDisposition">
                        <i class="ti ti-gavel me-1"></i> Beri Instruksi Dekan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Metrics Realisasi Kelas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Realisasi Pertemuan</div>
                <div class="kpi-value text-primary"><?= $heldCount ?> <span class="text-muted fs-6">/ <?= $plannedCount ?></span></div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-<?= $realizationPct < 50 ? 'danger' : ($realizationPct < 75 ? 'warning' : 'success') ?>" style="width: <?= min(100, $realizationPct) ?>%"></div>
                </div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                <i class="ti ti-calendar-stats fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Persentase Ketercapaian</div>
                <div class="kpi-value text-<?= $realizationPct < 50 ? 'danger' : ($realizationPct < 75 ? 'warning' : 'success') ?>"><?= $realizationPct ?>%</div>
                <div class="text-muted small mt-1">Standar BKD Min. 75% (12 Pertemuan)</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="ti ti-chart-bar fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Rata-rata Presensi Mahasiswa</div>
                <div class="kpi-value text-<?= $avgAttendance < 75 ? 'danger' : 'success' ?>"><?= $avgAttendance ?>%</div>
                <div class="text-muted small mt-1">Batas Minimal Ujian: 75%</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="ti ti-users-check fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Pertemuan Batal / Kendala</div>
                <div class="kpi-value text-<?= ($summary['cancelled_count'] ?? 0) > 0 ? 'danger' : 'muted' ?>"><?= (int)($summary['cancelled_count'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Sisa Terjadwal: <?= (int)($summary['planned_count'] ?? 0) ?></div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                <i class="ti ti-clock-pause fs-3"></i>
            </div>
        </div>
    </div>
</div>

<?php if ($class['problem_flag']): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between p-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                <i class="ti ti-alert-triangle fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-danger">Peringatan Radar Evaluasi Dekanat!</h6>
                <div class="small text-danger-emphasis"><?= htmlspecialchars($class['problem_notes'] ?? 'Kelas ini memiliki catatan khusus karena realisasi pertemuan atau presensi belum memenuhi ambang batas mutu.', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDekanDisposition">
            Tindak Lanjuti Dekan
        </button>
    </div>
<?php endif; ?>

<!-- Tabel Jurnal Perkuliahan Terperinci -->
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0">Jurnal & Log Tatap Muka Perkuliahan (1 s/d 16)</h5>
            <div class="card-subtitle text-muted small">Rekapitulasi tanggal, durasi waktu, topik bahasan, metode pengajaran, dan kehadiran mahasiswa</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/academic/export-lecture?format=csv" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-download me-1"></i> Ekspor CSV
            </a>
            <a href="<?= $baseUrl ?>/academic/export-lecture?format=print" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-printer me-1"></i> Cetak Laporan PDF
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" width="70">Ke-</th>
                        <th width="130">Tanggal & Jam</th>
                        <th width="110">Ruang</th>
                        <th>Topik & Pembahasan (RPS)</th>
                        <th width="130">Metode</th>
                        <th width="140">Presensi Mahasiswa</th>
                        <th width="120">Status</th>
                        <th class="text-end pe-4" width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($meetings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-notes-off fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada catatan pertemuan untuk kelas ini. Klik tombol <strong>Catat Pertemuan Baru</strong> di atas untuk membuat entri awal.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($meetings as $m): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">#<?= $m['meeting_no'] ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($m['meeting_date'])) ?></div>
                                    <div class="text-muted small"><?= substr($m['start_time'], 0, 5) ?> - <?= substr($m['end_time'], 0, 5) ?> WIB</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($m['room'] ?: $class['room'] ?: 'R. Kelas', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($m['topic'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($m['subtopic'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 320px;"><?= htmlspecialchars($m['subtopic'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($m['lecturer_notes'])): ?>
                                        <div class="small text-info mt-1"><i class="ti ti-info-circle me-1"></i><?= htmlspecialchars($m['lecturer_notes'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?= htmlspecialchars($m['delivery_method'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'Terlaksana'): ?>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <strong><?= $m['student_present'] ?> / <?= $m['student_total'] ?></strong>
                                            <span class="badge bg-<?= $m['attendance_percentage'] < 75 ? 'danger' : 'success' ?>-subtle text-<?= $m['attendance_percentage'] < 75 ? 'danger' : 'success' ?>">
                                                <?= $m['attendance_percentage'] ?>%
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-<?= $m['attendance_percentage'] < 75 ? 'danger' : 'success' ?>" style="width: <?= min(100, $m['attendance_percentage']) ?>%"></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'Terlaksana'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ti ti-check me-1"></i>Terlaksana</span>
                                    <?php elseif ($m['status'] === 'Batal'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-x me-1"></i>Batal</span>
                                    <?php elseif ($m['status'] === 'Pengganti / Reschedule'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="ti ti-clock-edit me-1"></i>Reschedule</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary"><i class="ti ti-calendar-time me-1"></i>Direncanakan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditMeeting<?= $m['id'] ?>" title="Edit Pertemuan">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <form action="<?= $baseUrl ?>/academic/meeting/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pertemuan ini?');">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-crud-sm" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>

                                    <!-- Modal Edit Pertemuan -->
                                    <div class="modal fade" id="modalEditMeeting<?= $m['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-body border-bottom py-3">
                                                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                        <i class="ti ti-pencil text-primary"></i> Edit Log Pertemuan Ke-<?= $m['meeting_no'] ?>
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="<?= $baseUrl ?>/academic/meeting/update" method="POST">
                                                    <?= CsrfHelper::tokenField() ?>
                                                    <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Tanggal Perkuliahan</label>
                                                                <input type="date" name="meeting_date" class="form-control" value="<?= $m['meeting_date'] ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Waktu Mulai - Selesai</label>
                                                                <div class="input-group">
                                                                    <input type="time" name="start_time" class="form-control" value="<?= substr($m['start_time'], 0, 5) ?>">
                                                                    <span class="input-group-text">-</span>
                                                                    <input type="time" name="end_time" class="form-control" value="<?= substr($m['end_time'], 0, 5) ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Ruang Kuliah</label>
                                                                <input type="text" name="room" class="form-control" value="<?= htmlspecialchars($m['room'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Topik Bahasan Sesuai RPS <span class="text-danger">*</span></label>
                                                            <input type="text" name="topic" class="form-control" value="<?= htmlspecialchars($m['topic'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Subtopik / Capaian Pembelajaran</label>
                                                            <textarea name="subtopic" class="form-control" rows="2"><?= htmlspecialchars($m['subtopic'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Metode Perkuliahan</label>
                                                                <select name="delivery_method" class="form-select">
                                                                    <option value="Tatap Muka" <?= $m['delivery_method'] === 'Tatap Muka' ? 'selected' : '' ?>>Tatap Muka (Luring)</option>
                                                                    <option value="Daring" <?= $m['delivery_method'] === 'Daring' ? 'selected' : '' ?>>Daring (Online)</option>
                                                                    <option value="Hybrid" <?= $m['delivery_method'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                                                    <option value="Praktikum / Lab" <?= $m['delivery_method'] === 'Praktikum / Lab' ? 'selected' : '' ?>>Praktikum / Lab</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Mahasiswa Hadir</label>
                                                                <div class="input-group">
                                                                    <input type="number" name="student_present" class="form-control" value="<?= $m['student_present'] ?>" min="0">
                                                                    <span class="input-group-text">dari</span>
                                                                    <input type="number" name="student_total" class="form-control" value="<?= $m['student_total'] ?>" min="1">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-semibold small text-muted">Status Pelaksanaan</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="Terlaksana" <?= $m['status'] === 'Terlaksana' ? 'selected' : '' ?>>Terlaksana</option>
                                                                    <option value="Direncanakan" <?= $m['status'] === 'Direncanakan' ? 'selected' : '' ?>>Direncanakan</option>
                                                                    <option value="Pengganti / Reschedule" <?= $m['status'] === 'Pengganti / Reschedule' ? 'selected' : '' ?>>Pengganti / Reschedule</option>
                                                                    <option value="Batal" <?= $m['status'] === 'Batal' ? 'selected' : '' ?>>Batal</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Catatan Dosen / Kendala Mengajar</label>
                                                            <input type="text" name="lecturer_notes" class="form-control" value="<?= htmlspecialchars($m['lecturer_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-body border-top py-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                                            <i class="ti ti-check me-1"></i> Simpan Perubahan
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

<!-- Modal Tambah Pertemuan Baru -->
<div class="modal fade" id="modalAddMeeting" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-body border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-plus text-primary"></i> Catat Pertemuan Perkuliahan Baru
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/academic/meeting/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Pertemuan Ke-</label>
                            <input type="number" name="meeting_no" class="form-control" value="<?= count($meetings) + 1 ?>" min="1" max="16" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Tanggal</label>
                            <input type="date" name="meeting_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Jam Mulai - Selesai</label>
                            <div class="input-group">
                                <input type="time" name="start_time" class="form-control" value="08:00">
                                <span class="input-group-text">-</span>
                                <input type="time" name="end_time" class="form-control" value="10:30">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Ruangan</label>
                            <input type="text" name="room" class="form-control" value="<?= htmlspecialchars($class['room'] ?? 'Lab 301', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Topik Pembahasan <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control" placeholder="Contoh: Implementasi Algoritma Supervised Learning" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Subtopik / Capaian Pembelajaran</label>
                        <textarea name="subtopic" class="form-control" rows="2" placeholder="Uraian poin materi bahasan dan kegiatan hands-on..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Metode Perkuliahan</label>
                            <select name="delivery_method" class="form-select">
                                <option value="Tatap Muka">Tatap Muka (Luring)</option>
                                <option value="Daring">Daring (Online)</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Praktikum / Lab">Praktikum / Lab</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Presensi Mahasiswa</label>
                            <div class="input-group">
                                <input type="number" name="student_present" class="form-control" value="32" min="0">
                                <span class="input-group-text">dari</span>
                                <input type="number" name="student_total" class="form-control" value="35" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="Terlaksana">Terlaksana</option>
                                <option value="Direncanakan">Direncanakan</option>
                                <option value="Pengganti / Reschedule">Pengganti / Reschedule</option>
                                <option value="Batal">Batal</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Catatan Dosen / Catatan Pelaksanaan</label>
                        <input type="text" name="lecturer_notes" class="form-control" placeholder="Catatan keaktifan kelas, tugas mandiri, dll">
                    </div>
                </div>
                <div class="modal-footer bg-body border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="ti ti-plus me-1"></i> Simpan Log Pertemuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Instruksi Dekan -->
<div class="modal fade" id="modalDekanDisposition" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-body border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-gavel text-primary"></i> Instruksi & Disposisi Dekanat
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/academic/action-class" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                <div class="modal-body p-4">
                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                        <div class="fw-bold text-dark"><?= htmlspecialchars($class['course_name'], ENT_QUOTES, 'UTF-8') ?> - Kelas <?= $class['class_name'] ?></div>
                        <div class="small text-muted">Dosen: <?= htmlspecialchars($class['lecturer_name'], ENT_QUOTES, 'UTF-8') ?> &bull; Realisasi: <?= $heldCount ?>/<?= $plannedCount ?> (<?= $realizationPct ?>%)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tindakan Dekan</label>
                        <select name="action_type" class="form-select" required>
                            <option value="disposition" <?= $class['problem_flag'] ? 'selected' : '' ?>>⚠️ Terbitkan Peringatan / Instruksi Dekanat</option>
                            <option value="resolve" <?= !$class['problem_flag'] ? 'selected' : '' ?>>✅ Selesaikan Masalah & Tandai Normal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Catatan Instruksi / Evaluasi Dekan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan arahan atau teguran dekanat terkait realisasi tatap muka..."><?= htmlspecialchars($class['problem_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Keputusan Dekan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
