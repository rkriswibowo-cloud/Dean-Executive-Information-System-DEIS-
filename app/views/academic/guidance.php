<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Monitoring Bimbingan (DPA / Skripsi / Magang / MBKM)</h5>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/academic/guidance?type=' + this.value">
                <option value="">Semua Jenis Bimbingan</option>
                <option value="Skripsi" <?= $filterType === 'Skripsi' ? 'selected' : '' ?>>Skripsi / Tugas Akhir</option>
                <option value="MBKM" <?= $filterType === 'MBKM' ? 'selected' : '' ?>>MBKM / MSIB</option>
                <option value="Magang" <?= $filterType === 'Magang' ? 'selected' : '' ?>>Magang Industri</option>
                <option value="DPA" <?= $filterType === 'DPA' ? 'selected' : '' ?>>Dosen Pembimbing Akademik</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Jenis</th>
                        <th>Mahasiswa</th>
                        <th>Judul / Topik Bimbingan</th>
                        <th>Dosen Pembimbing</th>
                        <th>Progres</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guidances as $g): ?>
                        <tr class="<?= $g['status'] === 'Terlambat' ? 'table-danger' : '' ?>">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border"><?= $g['type'] ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($g['student_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted"><?= $g['nim'] ?> • <?= $g['program_name'] ?></small>
                            </td>
                            <td style="max-width: 280px;">
                                <div class="small fw-semibold text-dark"><?= htmlspecialchars($g['title'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($g['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <strong><?= $g['progress_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-<?= $g['status'] === 'Terlambat' ? 'danger' : 'primary' ?>" style="width: <?= $g['progress_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($g['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
