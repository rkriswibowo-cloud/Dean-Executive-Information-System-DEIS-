<div class="text-center mb-4">
    <h4 class="fw-bold text-uppercase mb-1">LAPORAN EKSEKUTIF CAPAIAN KINERJA FAKULTAS</h4>
    <p class="mb-0">Periode Tahun Akademik 2025/2026 • Tahun Anggaran 2026</p>
</div>

<h6 class="fw-bold mb-2">I. CAPAIAN INDIKATOR KINERJA UTAMA (IKU)</h6>
<table class="table table-bordered table-sm mb-4">
    <thead class="table-light">
        <tr>
            <th width="80">Kode</th>
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
                <td class="fw-bold"><?= $ind['code'] ?></td>
                <td><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $ind['target_value'] ?> <?= $ind['unit'] ?></td>
                <td><?= $ind['realization_value'] ?? 0 ?> <?= $ind['unit'] ?></td>
                <td><?= $ind['achievement_percentage'] ?? 0 ?>%</td>
                <td><?= $ind['realization_status'] ?? 'Proses' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h6 class="fw-bold mb-2">II. KOMPARASI PROGRAM STUDI</h6>
<table class="table table-bordered table-sm mb-4">
    <thead class="table-light">
        <tr>
            <th>Program Studi</th>
            <th>Jenjang</th>
            <th>Peringkat Akreditasi</th>
            <th>Jumlah Mahasiswa</th>
            <th>Jumlah Dosen</th>
            <th>Target Retensi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($programs as $p): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $p['degree'] ?></td>
                <td><?= $p['accreditation_status'] ?></td>
                <td><?= number_format($p['student_count']) ?> Orang</td>
                <td><?= number_format($p['lecturer_count']) ?> Orang</td>
                <td><?= $p['target_retention'] ?>%</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
