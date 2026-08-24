<?php use App\Helpers\FormatHelper; ?>
<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card card-lg shadow-sm border-0 rounded-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">Tracer Study & Keterserapan Alumni</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Nama Alumni & NIM</th>
                                <th>IPK Lulus</th>
                                <th>Status Pekerjaan / Industri</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumni as $a): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <small class="text-muted">NIM: <?= $a['nim'] ?></small>
                                    </td>
                                    <td><strong><?= $a['current_gpa'] ?></strong></td>
                                    <td><?= htmlspecialchars($a['organization'] ?? 'Bekerja di Sektor IT', ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">Kepuasan Pengguna Lulusan</h5>
            </div>
            <div class="card-body p-4">
                <?php foreach ($employerSurveys as $es): ?>
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Skor Kepuasan Industri</span>
                            <span class="badge bg-success"><?= $es['satisfaction_percentage'] ?>%</span>
                        </div>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($es['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
