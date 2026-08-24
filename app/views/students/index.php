<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Total Mahasiswa Aktif</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $totalStudents ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-danger">Risiko Kritis (Critical)</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= $criticalCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold text-warning">Perlu Perhatian (Warning)</span>
            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $warningCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-lg shadow-sm border-0 rounded-4 p-3">
            <span class="text-muted small fw-semibold">Penerima Beasiswa</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $scholarshipCount ?> <span class="fs-6 fw-normal text-muted">Mahasiswa</span></h3>
        </div>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Database Mahasiswa & Status Akademik</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/students/early-warning" class="btn btn-sm btn-outline-danger btn-crud-sm">
                <i class="ti ti-alert-triangle me-1"></i> Early Warning System
            </a>
            <a href="<?= $baseUrl ?>/students/alumni" class="btn btn-sm btn-outline-secondary btn-crud-sm">
                <i class="ti ti-school me-1"></i> Tracer Study & Alumni
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></div>
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
                                <button type="button" class="btn btn-sm <?= $s['risk_status'] === 'Critical' ? 'btn-danger' : ($s['risk_status'] === 'Warning' ? 'btn-warning text-dark' : 'btn-outline-primary') ?> btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalStudentAction<?= $s['id'] ?>">
                                    <i class="ti ti-shield-alert me-1"></i> Intervensi EWS
                                </button>

                                <!-- Modal Intervensi Mahasiswa -->
                                <div class="modal fade" id="modalStudentAction<?= $s['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-heart-handshake text-danger"></i> Intervensi Dekanat & EWS Mahasiswa
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/students/action" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?> (NIM: <?= $s['nim'] ?>)</div>
                                                        <div class="small text-muted">Prodi: <?= htmlspecialchars($s['program_name'], ENT_QUOTES, 'UTF-8') ?> | IPK: <?= $s['current_gpa'] ?> | Presensi: <?= $s['attendance_percentage'] ?>%</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Penetapan Tingkat Risiko</label>
                                                        <select name="risk_status" class="form-select" required>
                                                            <option value="Normal" <?= $s['risk_status'] === 'Normal' ? 'selected' : '' ?>>🟢 Normal (Aman / Tidak Perlu Tindakan)</option>
                                                            <option value="Warning" <?= $s['risk_status'] === 'Warning' ? 'selected' : '' ?>>🟡 Warning (Perhatian / Disposisi Konseling DPA)</option>
                                                            <option value="Critical" <?= $s['risk_status'] === 'Critical' ? 'selected' : '' ?>>🔴 Critical (Kritis / Surat Peringatan Dekanat / Panggilan Wali)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan Tindakan / Surat Keputusan Dekanat</label>
                                                        <textarea name="action_notes" class="form-control" rows="3" placeholder="Contoh: Penerbitan Surat Peringatan (SP-1) Dekan dan pemanggilan orang tua / rekomendasi cuti studi..."><?= htmlspecialchars($s['risk_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                                        <i class="ti ti-send me-1"></i> Terbitkan Intervensi Dekan
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
