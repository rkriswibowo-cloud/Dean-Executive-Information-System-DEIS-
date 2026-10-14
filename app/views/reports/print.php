<div class="text-center mb-4">
    <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">LAPORAN EKSEKUTIF CAPAIAN KINERJA FAKULTAS</h4>
    <p class="mb-0 text-muted" style="font-size: 10pt;">Periode Tahun Akademik 2025/2026 &bull; Tahun Anggaran 2026</p>
</div>

<h6 class="fw-bold mb-2 text-uppercase" style="font-size: 10.5pt;">I. Capaian Indikator Kinerja Utama (IKU 2026)</h6>
<table class="table table-bordered table-sm mb-4" style="font-size: 9.5pt;">
    <thead class="table-light">
        <tr class="text-center align-middle">
            <th width="70">Kode</th>
            <th>Indikator Kinerja Utama</th>
            <th width="100">Target</th>
            <th width="100">Realisasi</th>
            <th width="90">Capaian</th>
            <th width="100">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($indicators as $ind): ?>
            <tr>
                <td class="fw-bold text-center"><?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $ind['target_value'] ?> <?= $ind['unit'] ?></td>
                <td class="text-center"><?= $ind['realization_value'] ?? 0 ?> <?= $ind['unit'] ?></td>
                <td class="text-center fw-bold"><?= $ind['achievement_percentage'] ?? 0 ?>%</td>
                <td class="text-center"><?= htmlspecialchars($ind['realization_status'] ?? 'Proses', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h6 class="fw-bold mb-2 text-uppercase" style="font-size: 10.5pt;">II. Komparasi Kinerja Program Studi</h6>
<table class="table table-bordered table-sm mb-4" style="font-size: 9.5pt;">
    <thead class="table-light">
        <tr class="text-center align-middle">
            <th>Program Studi</th>
            <th width="80">Jenjang</th>
            <th width="140">Akreditasi &amp; Skor</th>
            <th width="120">Mahasiswa Aktif</th>
            <th width="110">Dosen Tetap</th>
            <th width="110">Target Retensi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totMhs = 0;
        $totDosen = 0;
        foreach ($programs as $p): 
            $totMhs += ($p['student_count'] ?? 0);
            $totDosen += ($p['lecturer_count'] ?? 0);
        ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($p['degree'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($p['accreditation_status'], ENT_QUOTES, 'UTF-8') ?> (<?= $p['accreditation_score'] ?>)</td>
                <td class="text-end"><?= number_format($p['student_count']) ?> Org</td>
                <td class="text-end"><?= number_format($p['lecturer_count']) ?> Org</td>
                <td class="text-center"><?= $p['target_retention'] ?>%</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light fw-bold">
        <tr>
            <td colspan="3" class="text-center">TOTAL FAKULTAS</td>
            <td class="text-end"><?= number_format($totMhs) ?> Org</td>
            <td class="text-end"><?= number_format($totDosen) ?> Org</td>
            <td class="text-center">-</td>
        </tr>
    </tfoot>
</table>

<?php if (!empty($lecturers)): ?>
<h6 class="fw-bold mb-2 text-uppercase" style="font-size: 10.5pt;">III. Rekapitulasi Profil SDM Dosen Tetap</h6>
<table class="table table-bordered table-sm mb-4" style="font-size: 9pt;">
    <thead class="table-light">
        <tr class="text-center align-middle">
            <th width="35">No</th>
            <th>Nama Dosen &amp; Gelar</th>
            <th width="100">NIDN</th>
            <th>Homebase Prodi</th>
            <th width="110">Jab. Fungsional</th>
            <th width="70">Pendidikan</th>
            <th width="90">Sertifikasi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lecturers as $idx => $l): ?>
            <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($l['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center font-monospace"><?= htmlspecialchars($l['nidn'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($l['program_name'] ?? 'FTIK', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($l['academic_rank'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($l['highest_degree'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($l['certification_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

