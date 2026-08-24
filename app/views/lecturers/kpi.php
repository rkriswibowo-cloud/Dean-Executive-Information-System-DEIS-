<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="ti ti-trophy text-warning fs-3"></i> Ranking & Skor KPI Dosen Fakultas
        </h5>
        <a href="<?= $baseUrl ?>/lecturers" class="btn btn-sm btn-outline-secondary">← Daftar Dosen</a>
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
                        <th class="text-end pe-4">Total Skor KPI</th>
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
                            <td class="text-end pe-4">
                                <span class="badge bg-primary fs-6 px-3 py-1"><?= number_format($r['kpi_total_score'], 1) ?></span>
                            </td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
