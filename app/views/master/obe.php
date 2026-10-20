<?php
use App\Helpers\CsrfHelper;

$labels = [
    'academic-years' => 'Tahun Akademik', 'lecturers' => 'Dosen', 'students' => 'Mahasiswa',
    'study-programs' => 'Program Studi', 'curricula' => 'Kurikulum',
    'curriculum-rules' => 'Aturan Kurikulum', 'graduate-profiles' => 'Profil Lulusan',
    'learning-outcomes' => 'CPL', 'courses' => 'Mata Kuliah',
    'rubrics' => 'Rubrik Penilaian', 'imports' => 'Import Data'
];
$obePaths = [
    'academic-years' => 'tahun-akademik', 'lecturers' => 'dosen', 'students' => 'mahasiswa',
    'study-programs' => 'prodi', 'curricula' => 'kurikulum',
    'curriculum-rules' => 'aturan-kurikulum', 'graduate-profiles' => 'profil-lulusan',
    'learning-outcomes' => 'cpl', 'courses' => 'mata-kuliah',
    'rubrics' => 'rubrik', 'imports' => 'import'
];
$columns = match ($section) {
    'academic-years' => ['obe_id'=>'ID OBE','name'=>'Tahun Akademik','is_active'=>'Status Aktif'],
    'lecturers' => ['obe_id'=>'ID OBE','name'=>'Nama Dosen','nidn'=>'NIDN','study_program_name'=>'Program Studi','email'=>'Email'],
    'students' => ['obe_id'=>'ID OBE','username'=>'NIM/Username','name'=>'Nama Mahasiswa','study_program_name'=>'Program Studi','email'=>'Email'],
    'study-programs' => ['obe_id'=>'ID OBE','code'=>'Kode','name'=>'Program Studi','degree'=>'Jenjang','head_name'=>'Kaprodi','status'=>'Status'],
    'curricula' => ['obe_id'=>'ID OBE','code'=>'Kode','name'=>'Kurikulum','study_program_name'=>'Program Studi','start_year'=>'Mulai','version'=>'Versi','status'=>'Status'],
    'curriculum-rules' => ['id'=>'ID OBE','prodi_nama'=>'Program Studi','kurikulum_nama'=>'Kurikulum','angkatan_mulai'=>'Angkatan Mulai','angkatan_selesai'=>'Angkatan Selesai','keterangan'=>'Keterangan'],
    'graduate-profiles' => ['id'=>'ID OBE','kode'=>'Kode','nama'=>'Profil Lulusan','prodi_nama'=>'Program Studi','kurikulum_nama'=>'Kurikulum','deskripsi'=>'Deskripsi'],
    'learning-outcomes' => ['id'=>'ID OBE','kode'=>'Kode','rumusan'=>'Rumusan CPL','kategori'=>'Kategori','prodi_nama'=>'Program Studi','target_capaian'=>'Target'],
    'courses' => ['obe_id'=>'ID OBE','code'=>'Kode','name'=>'Mata Kuliah','study_program_name'=>'Program Studi','curriculum_name'=>'Kurikulum','credits'=>'SKS','semester'=>'Semester','status'=>'Status'],
    'rubrics' => ['id'=>'ID OBE','kode_jenis'=>'Kode','nama'=>'Rubrik','jenis'=>'Jenis','criteria_count'=>'Kriteria','level_count'=>'Level','status'=>'Status'],
    default => []
};
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#312e81,#4f46e5,#2563eb)">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <span class="badge bg-white text-primary mb-2"><i class="fa-solid fa-lock me-1"></i>READ-ONLY DARI OBE</span>
                <h3 class="fw-bold text-white mb-1">Master Data Akademik OBE</h3>
                <p class="mb-0 text-white-50">DEIS hanya membaca data. Penambahan dan perubahan master dilakukan di aplikasi OBE.</p>
            </div>
            <div class="text-end">
                <?php if (!empty($health['available'])): ?>
                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i>OBE Terhubung · <?= htmlspecialchars((string)$health['latency_ms']) ?> ms</span>
                <?php else: ?>
                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>OBE Tidak Tersedia</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 d-flex gap-2 flex-wrap">
        <?php foreach ($sections as $item): ?>
            <a href="<?= $baseUrl ?>/master/obe?section=<?= urlencode($item) ?>" class="btn btn-sm <?= $section === $item ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                <?= htmlspecialchars($labels[$item]) ?>
                <?php if ($counts[$item] !== null): ?><span class="badge bg-white text-dark ms-1"><?= (int)$counts[$item] ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($labels[$section]) ?></h5>
            <div class="small text-muted">Sumber langsung: database OBE · tidak dapat diedit dari DEIS</div>
        </div>
        <div class="d-flex gap-2">
            <?php if (in_array($currentRole, ['super_admin','dekan','developer'], true)): ?>
                <a href="<?= $baseUrl ?>/master/obe/status" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-stethoscope me-1"></i>Status Integrasi
                </a>
            <?php endif; ?>
            <?php if (in_array($currentRole, ['super_admin','developer'], true)): ?>
                <form action="<?= $baseUrl ?>/master/obe/reconcile" method="POST">
                    <?= CsrfHelper::tokenField() ?>
                    <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-link me-1"></i>Rekonsiliasi ID</button>
                </form>
            <?php endif; ?>
            <a href="http://localhost/obe/<?= htmlspecialchars($obePaths[$section]) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Kelola di OBE
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if ($section === 'imports'): ?>
            <div class="p-5 text-center">
                <i class="fa-solid fa-file-import fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">Import data hanya dilakukan di OBE</h5>
                <p class="text-muted">DEIS tidak menduplikasi proses import untuk mencegah perbedaan master akademik.</p>
            </div>
        <?php elseif (empty($health['available'])): ?>
            <div class="p-5 text-center text-danger">
                <i class="fa-solid fa-triangle-exclamation fa-2x mb-3"></i>
                <div class="fw-bold">Data OBE tidak dapat dibaca</div>
                <div class="small text-muted mt-1">DEIS tidak menggunakan data master lokal sebagai fallback. Hubungi administrator untuk memeriksa koneksi OBE.</div>
            </div>
        <?php elseif (empty($records)): ?>
            <div class="p-5 text-center text-muted">Belum ada data pada master ini di OBE.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" data-auto-paginate="15">
                    <thead class="bg-body-tertiary text-muted small text-uppercase"><tr>
                        <?php foreach ($columns as $label): ?><th><?= htmlspecialchars($label) ?></th><?php endforeach; ?>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($records as $record): ?><tr>
                        <?php foreach ($columns as $key => $label): ?>
                            <?php $value = $record[$key] ?? null; ?>
                            <td>
                                <?php if ($key === 'is_active'): ?>
                                    <span class="badge <?= $value ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= $value ? 'Aktif' : 'Tidak Aktif' ?></span>
                                <?php elseif ($key === 'status'): ?>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars((string)($value ?? '-')) ?></span>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8')) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr><?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($mappingSummary)): ?>
        <div class="card-footer bg-transparent small text-muted">
            Mapping ID tersimpan di DEIS: <?= array_sum(array_map(static fn($row) => (int)($row['mapped'] ?? 0), $mappingSummary)) ?> terpetakan.
        </div>
    <?php endif; ?>
</div>
