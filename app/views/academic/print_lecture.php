<div class="text-center mb-4">
    <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">LAPORAN MONITORING TATAP MUKA PERKULIAHAN & PRESENSI KELAS</h4>
    <p class="mb-0 text-muted" style="font-size: 10pt;">
        Periode: <?= htmlspecialchars($activeAy['name'] ?? '2025/2026', ENT_QUOTES, 'UTF-8') ?> - Semester <?= htmlspecialchars($activeAy['semester'] ?? 'Genap', ENT_QUOTES, 'UTF-8') ?> &bull; Dicetak pada: <?= date('d/m/Y H:i') ?> WIB
    </p>
</div>

<table class="table table-bordered table-sm mb-4" style="font-size: 9pt;">
    <thead class="table-light">
        <tr class="text-center align-middle">
            <th width="40">No</th>
            <th width="90">Kode</th>
            <th>Mata Kuliah</th>
            <th width="60">SKS</th>
            <th width="60">Kelas</th>
            <th>Dosen Pengampu</th>
            <th>Program Studi</th>
            <th width="100">Realisasi Pertemuan</th>
            <th width="90">Rata-rata Presensi</th>
            <th width="100">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        foreach ($classes as $c): 
            $pct = round(($c['total_held_meetings'] / ($c['total_planned_meetings'] ?: 1)) * 100, 1);
        ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center fw-bold"><?= htmlspecialchars($c['course_code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $c['sks'] ?></td>
                <td class="text-center fw-bold"><?= htmlspecialchars($c['class_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['program_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                    <strong><?= $c['total_held_meetings'] ?></strong> / <?= $c['total_planned_meetings'] ?>
                    (<?= $pct ?>%)
                </td>
                <td class="text-center fw-bold"><?= $c['average_attendance'] ?>%</td>
                <td class="text-center">
                    <?php if ($c['problem_flag']): ?>
                        <span class="badge bg-danger">Bermasalah</span>
                    <?php else: ?>
                        <span class="badge bg-success">Normal</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="row mt-4" style="font-size: 9.5pt;">
    <div class="col-7">
        <p class="mb-1 text-muted"><strong>Catatan Pengawasan Mutu Akademik:</strong></p>
        <ul class="text-muted ps-3 small">
            <li>Batas minimum pemenuhan BKD pengajaran adalah 75% tatap muka (12 dari 16 pertemuan).</li>
            <li>Mahasiswa dengan tingkat kehadiran di bawah 75% tidak berhak mengikuti Ujian Akhir Semester (UAS).</li>
        </ul>
    </div>
    <div class="col-5 text-center">
        <p class="mb-5">Mengetahui,<br><strong>Dekan Fakultas</strong></p>
        <p class="fw-bold text-decoration-underline mb-0">Dr. Ir. Hendra Gunawan, M.Kom.</p>
        <p class="text-muted small">NIP. 197805122003121002</p>
    </div>
</div>
