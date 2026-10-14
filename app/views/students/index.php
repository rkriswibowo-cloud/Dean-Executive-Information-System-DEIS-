<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Mahasiswa Aktif</div>
                <div class="kpi-value"><?= number_format($totalStudents) ?></div>
                <div class="text-muted small mt-1">Terdaftar di Fakultas</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title text-danger">Risiko Kritis (EWS)</div>
                <div class="kpi-value text-danger"><?= $criticalCount ?></div>
                <div class="text-muted small mt-1">Perlu Intervensi Dekan</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title text-warning">Perlu Perhatian</div>
                <div class="kpi-value text-warning"><?= $warningCount ?></div>
                <div class="text-muted small mt-1">Monitoring Pembimbing</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div>
                <div class="kpi-title text-success">Penerima Beasiswa</div>
                <div class="kpi-value text-success"><?= $scholarshipCount ?></div>
                <div class="text-muted small mt-1">Prestasi & Afirmasi</div>
            </div>
            <div class="kpi-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="fa-solid fa-award"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0">Database Mahasiswa & Status Akademik</h5>
            <div class="card-subtitle">Data akademik, IPK kumulatif, presensi perkuliahan, dan status risiko kelulusan</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/students/early-warning" class="btn btn-sm btn-soft-danger btn-crud-sm">
                <i class="fa-solid fa-triangle-exclamation"></i> Early Warning System
            </a>
            <a href="<?= $baseUrl ?>/students/alumni" class="btn btn-sm btn-outline-secondary btn-crud-sm">
                <i class="fa-solid fa-user-tie"></i> Tracer Study & Alumni
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">NIM & Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Angkatan & Sem</th>
                        <th>IPK Kumulatif</th>
                        <th>SKS Selesai</th>
                        <th>Presensi</th>
                        <th>Beasiswa / Prestasi</th>
                        <th>Status Risiko</th>
                        <th class="text-end pe-4">Aksi Dekanat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr class="<?= $s['risk_status'] === 'Critical' ? 'table-danger' : ($s['risk_status'] === 'Warning' ? 'table-warning' : '') ?>">
                            <td class="ps-4">
                                <div class="fw-bold"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted">NIM: <?= $s['nim'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($s['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Angkatan <?= $s['batch_year'] ?> (Sem <?= $s['semester'] ?>)</td>
                            <td><strong><?= $s['current_gpa'] ?></strong> / 4.00</td>
                            <td><?= $s['credits_earned'] ?> SKS</td>
                            <td><?= $s['attendance_percentage'] ?>%</td>
                            <td>
                                <?php if (!empty($s['scholarship'])): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $s['scholarship'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($s['organization'])): ?>
                                    <small class="d-block text-muted"><?= $s['organization'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= FormatHelper::statusBadge($s['risk_status']) ?></td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm <?= $s['risk_status'] === 'Critical' ? 'btn-soft-danger' : ($s['risk_status'] === 'Warning' ? 'btn-soft-warning' : 'btn-soft-primary') ?> btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalStudentAction<?= $s['id'] ?>">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Intervensi EWS
                                </button>

                                <!-- Modal Intervensi Mahasiswa -->
                                <div class="modal fade" id="modalStudentAction<?= $s['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header d-flex align-items-center justify-content-between">
                                                <h5 class="modal-title d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-hand-holding-heart text-danger"></i> Intervensi Dekanat & EWS Mahasiswa
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/students/action" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="p-3 rounded-3 mb-3 border" style="background: var(--surface-hover);">
                                                        <div class="fw-bold"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?> (NIM: <?= $s['nim'] ?>)</div>
                                                        <div class="small text-muted">Prodi: <?= htmlspecialchars($s['program_name'], ENT_QUOTES, 'UTF-8') ?> | IPK: <?= $s['current_gpa'] ?> | Presensi: <?= $s['attendance_percentage'] ?>%</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Penetapan Tingkat Risiko</label>
                                                        <select name="risk_status" class="form-select" required>
                                                            <option value="Normal" <?= $s['risk_status'] === 'Normal' ? 'selected' : '' ?>>🟢 Normal (Aman / Tidak Perlu Tindakan)</option>
                                                            <option value="Warning" <?= $s['risk_status'] === 'Warning' ? 'selected' : '' ?>>🟡 Warning (Perhatian / Disposisi Konseling DPA)</option>
                                                            <option value="Critical" <?= $s['risk_status'] === 'Critical' ? 'selected' : '' ?>>🔴 Critical (Kritis / Surat Peringatan Dekanat / Panggilan Wali)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Tindakan / Surat Keputusan Dekanat</label>
                                                        <textarea name="action_notes" class="form-control" rows="3" placeholder="Contoh: Penerbitan Surat Peringatan (SP-1) Dekan dan pemanggilan orang tua / rekomendasi cuti studi..."><?= htmlspecialchars($s['risk_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                                        <i class="fa-solid fa-paper-plane me-1"></i> Terbitkan Intervensi Dekan
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
