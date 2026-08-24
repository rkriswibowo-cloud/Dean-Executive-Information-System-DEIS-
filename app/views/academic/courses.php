<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Struktur Kurikulum & Kesiapan RPS</h5>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" onchange="location.href='<?= $baseUrl ?>/academic/courses?program_id=' + this.value">
                <option value="">Semua Program Studi</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterProgram == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Semester</th>
                        <th>Dosen Koordinator</th>
                        <th>Prodi</th>
                        <th class="text-end pe-4">Status RPS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $c['sks'] ?> SKS</td>
                            <td>Semester <?= $c['semester'] ?></td>
                            <td><?= htmlspecialchars($c['lecturer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end pe-4"><?= FormatHelper::statusBadge($c['rps_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
