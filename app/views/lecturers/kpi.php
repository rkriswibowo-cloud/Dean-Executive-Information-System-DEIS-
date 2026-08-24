<?php 
use App\Helpers\FormatHelper; 
use App\Helpers\CsrfHelper;
?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="ti ti-trophy text-warning fs-3"></i> Ranking & Skor KPI Dosen Fakultas
        </h5>
        <a href="<?= $baseUrl ?>/lecturers" class="btn btn-sm btn-outline-secondary btn-crud-sm">← Daftar Dosen</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Rank</th>
                        <th>Dosen</th>
                        <th>Prodi</th>
                        <th>SINTA Score</th>
                        <th>Publikasi</th>
                        <th>PkM</th>
                        <th>HKI</th>
                        <th>Presensi</th>
                        <th>Total Skor KPI</th>
                        <th class="text-end pe-4">Aksi Dekanat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($rankings as $r): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if ($rank === 1): ?>
                                    <span class="badge bg-warning text-dark fs-6 rounded-circle p-2">🥇</span>
                                <?php elseif ($rank === 2): ?>
                                    <span class="badge bg-secondary text-white fs-6 rounded-circle p-2">🥈</span>
                                <?php elseif ($rank === 3): ?>
                                    <span class="badge bg-danger-subtle text-danger fs-6 rounded-circle p-2">🥉</span>
                                <?php else: ?>
                                    <span class="fw-bold text-muted ps-2"><?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= $r['academic_rank'] ?> • <?= $r['education_level'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($r['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= $r['sinta_score'] ?></strong></td>
                            <td><?= $r['publication_count'] ?></td>
                            <td><?= $r['pkm_count'] ?></td>
                            <td><?= $r['hki_count'] ?></td>
                            <td><?= $r['attendance_percentage'] ?>%</td>
                            <td>
                                <span class="badge bg-primary fs-6 px-3 py-1"><?= number_format($r['kpi_total_score'], 1) ?></span>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalKpiAction<?= $r['id'] ?>">
                                    <i class="ti ti-medal me-1"></i> Reward / Aksi
                                </button>

                                <!-- Modal Aksi KPI Dosen -->
                                <div class="modal fade" id="modalKpiAction<?= $r['id'] ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-body border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="ti ti-award text-warning"></i> Penetapan Reward & Rekomendasi Dekanat
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= $baseUrl ?>/lecturers/action-kpi" method="POST">
                                                <?= CsrfHelper::tokenField() ?>
                                                <input type="hidden" name="lecturer_id" value="<?= $r['id'] ?>">
                                                <div class="modal-body p-4">
                                                    <div class="bg-body-tertiary p-3 rounded-3 mb-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?> (Peringkat #<?= $rank ?>)</div>
                                                        <div class="small text-muted">Prodi: <?= htmlspecialchars($r['program_name'], ENT_QUOTES, 'UTF-8') ?> | Total Skor KPI: <strong><?= number_format($r['kpi_total_score'], 1) ?></strong></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Jenis Keputusan Dekanat</label>
                                                        <select name="decision_type" class="form-select" required>
                                                            <option value="Reward" <?= $rank <= 3 ? 'selected' : '' ?>>🏆 Penghargaan Dekanat (Dosen Berprestasi / Piagam)</option>
                                                            <option value="Incentive">💰 Penetapan Insentif Publikasi / Dana Hibah Riset</option>
                                                            <option value="Guidance">📋 Rekomendasi Peningkatan Kinerja / Pembinaan</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Catatan & Rincian Keputusan Dekan</label>
                                                        <textarea name="action_notes" class="form-control" rows="3" placeholder="Contoh: Diberikan insentif publikasi Scopus Q1 sebesar Rp 5.000.000,- dan piagam penghargaan..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-body border-top py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-semibold">
                                                        <i class="ti ti-check me-1"></i> Simpan Keputusan Dekan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
