<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Hasil Survei Kepuasan Layanan Stakeholder Fakultas</h5>
        <a href="<?= $baseUrl ?>/quality" class="btn btn-sm btn-outline-secondary">← Standar SPMI</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kategori Responden</th>
                        <th>Tahun</th>
                        <th>Jumlah Responden</th>
                        <th>Rata-rata Skor (4.0)</th>
                        <th>Persentase Kepuasan</th>
                        <th>Kesimpulan & Ringkasan</th>
                        <th class="text-end pe-4">Kategori Mutu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surveys as $s): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($s['category'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $s['period_year'] ?></td>
                            <td><strong><?= number_format($s['respondents_count']) ?></strong> Orang</td>
                            <td><strong class="text-primary"><?= $s['average_score'] ?></strong> / 4.00</td>
                            <td>
                                <strong><?= $s['satisfaction_percentage'] ?>%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: <?= $s['satisfaction_percentage'] ?>%"></div>
                                </div>
                            </td>
                            <td style="max-width: 280px;">
                                <small class="text-muted"><?= htmlspecialchars($s['summary'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($s['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
