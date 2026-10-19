<div class="text-center mb-4">
    <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">LAPORAN AUDIT KESIAPAN RPS & KURIKULUM OBE FAKULTAS</h4>
    <p class="mb-0 text-muted" style="font-size: 10pt;">
        Penjaminan Mutu Pembelajaran & Capaian IKU-7 Dikti &bull; Dicetak pada: <?= date('d/m/Y H:i') ?> WIB
    </p>
</div>

<!-- Ringkasan Statistik -->
<div class="row mb-3" style="font-size: 9.5pt;">
    <div class="col-3">
        <strong>Total Mata Kuliah:</strong> <?= $summary['total_courses'] ?> MK
    </div>
    <div class="col-3">
        <strong>RPS Sah (Lengkap):</strong> <?= $summary['complete_count'] ?> MK (<?= round(($summary['complete_count'] / ($summary['total_courses'] ?: 1)) * 100, 1) ?>%)
    </div>
    <div class="col-3">
        <strong>Revisi / Belum:</strong> <?= $summary['revision_count'] + $summary['incomplete_count'] ?> MK
    </div>
    <div class="col-3">
        <strong>IKU-7 (Case Method):</strong> <?= $summary['case_method_count'] ?> MK
    </div>
</div>

<table class="table table-bordered table-sm mb-4" style="font-size: 9pt;">
    <thead class="table-light">
        <tr class="text-center align-middle">
            <th width="40">No</th>
            <th width="80">Kode</th>
            <th>Mata Kuliah</th>
            <th width="60">SKS</th>
            <th width="70">Smt</th>
            <th>Program Studi</th>
            <th>Dosen Koordinator</th>
            <th width="100">Status RPS</th>
            <th width="120">Kesesuaian CPL</th>
            <th width="110">IKU-7</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        foreach ($courses as $c): 
        ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center fw-bold"><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $c['sks'] ?></td>
                <td class="text-center"><?= $c['semester'] ?></td>
                <td><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['lecturer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                    <?php if ($c['rps_status'] === 'Lengkap'): ?>
                        <span class="badge bg-success">Lengkap</span>
                    <?php elseif ($c['rps_status'] === 'Revisi'): ?>
                        <span class="badge bg-warning text-dark">Revisi</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Belum Lengkap</span>
                    <?php endif; ?>
                </td>
                <td class="text-center small"><?= htmlspecialchars($c['cpl_status'] ?? 'Terpenuhi', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                    <?= !empty($c['case_method_flag']) ? '<strong>Ya (Case)</strong>' : '<span class="text-muted">Reguler</span>' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="row mt-4" style="font-size: 9.5pt;">
    <div class="col-7">
        <p class="mb-1 text-muted"><strong>Standar Mutu Kurikulum Dekanat:</strong></p>
        <ul class="text-muted ps-3 small">
            <li>Seluruh mata kuliah wajib memiliki RPS berbasis OBE sebelum semester perkuliahan dimulai.</li>
            <li>Minimal 50% mata kuliah wajib menerapkan metode Case Method atau Project-Based Learning (IKU-7).</li>
        </ul>
    </div>
    <div class="col-5 text-center">
        <p class="mb-5">Mengesahkan,<br><strong>Dekan Fakultas</strong></p>
        <p class="fw-bold text-decoration-underline mb-0">Dr. Ir. Hendra Gunawan, M.Kom.</p>
        <p class="text-muted small">NIP. 197805122003121002</p>
    </div>
</div>
