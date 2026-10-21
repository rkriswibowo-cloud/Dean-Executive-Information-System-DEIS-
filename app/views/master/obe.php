<?php
use App\Helpers\CsrfHelper;

$labels = [
    'academic-years' => 'Tahun Akademik', 'lecturers' => 'Dosen', 'students' => 'Mahasiswa',
    'study-programs' => 'Program Studi', 'curricula' => 'Kurikulum',
    'curriculum-rules' => 'Aturan Kurikulum', 'graduate-profiles' => 'Profil Lulusan',
    'learning-outcomes' => 'CPL', 'courses' => 'Mata Kuliah',
    'rubrics' => 'Rubrik Penilaian'
];
$obePaths = [
    'academic-years' => 'tahun-akademik', 'lecturers' => 'dosen', 'students' => 'mahasiswa',
    'study-programs' => 'prodi', 'curricula' => 'kurikulum',
    'curriculum-rules' => 'aturan-kurikulum', 'graduate-profiles' => 'profil-lulusan',
    'learning-outcomes' => 'cpl', 'courses' => 'mata-kuliah',
    'rubrics' => 'rubrik'
];
$columns = match ($section) {
    'academic-years' => ['name'=>'Tahun Akademik','is_active'=>'Status Aktif'],
    'lecturers' => ['name'=>'Nama Dosen','nidn'=>'NIDN','study_program_name'=>'Program Studi','email'=>'Email'],
    'students' => ['username'=>'NIM/Username','name'=>'Nama Mahasiswa','study_program_name'=>'Program Studi','email'=>'Email'],
    'study-programs' => ['code'=>'Kode','name'=>'Program Studi','degree'=>'Jenjang','head_name'=>'Kaprodi','status'=>'Status'],
    'curricula' => ['code'=>'Kode','name'=>'Kurikulum','study_program_name'=>'Program Studi','start_year'=>'Mulai','version'=>'Versi','status'=>'Status'],
    'curriculum-rules' => ['prodi_nama'=>'Program Studi','kurikulum_nama'=>'Kurikulum','angkatan_mulai'=>'Angkatan Mulai','angkatan_selesai'=>'Angkatan Selesai','keterangan'=>'Keterangan'],
    'graduate-profiles' => ['kode'=>'Kode','nama'=>'Profil Lulusan','prodi_nama'=>'Program Studi','kurikulum_nama'=>'Kurikulum','deskripsi'=>'Deskripsi'],
    'learning-outcomes' => ['kode'=>'Kode','rumusan'=>'Rumusan CPL','kategori'=>'Kategori','prodi_nama'=>'Program Studi','target_capaian'=>'Target'],
    'courses' => ['code'=>'Kode','name'=>'Mata Kuliah','study_program_name'=>'Program Studi','curriculum_name'=>'Kurikulum','credits'=>'SKS','semester'=>'Semester','status'=>'Status'],
    'rubrics' => ['kode_jenis'=>'Kode','nama'=>'Rubrik','jenis'=>'Jenis','criteria_count'=>'Kriteria','level_count'=>'Level','status'=>'Status'],
    default => []
};
?>

<div class="card obe-master-hero border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body obe-master-hero-body p-4 text-white">
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

<div class="card obe-master-tabs border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 d-flex gap-2 flex-wrap">
        <?php foreach ($sections as $item): ?>
            <a href="<?= $baseUrl ?>/master/obe?section=<?= urlencode($item) ?>" class="btn btn-sm <?= $section === $item ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                <?= htmlspecialchars($labels[$item]) ?>
                <?php if ($counts[$item] !== null): ?><span class="badge bg-white text-dark ms-1"><?= (int)$counts[$item] ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card obe-master-content border-0 shadow-sm rounded-4">
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

    <!-- Toolbar Filter & Pencarian Hierarki OBE -->
    <?php if (!empty($health['available'])): ?>
        <div class="px-4 py-3 bg-body-tertiary bg-opacity-50 border-bottom">
            <form method="GET" action="<?= $baseUrl ?>/master/obe" id="obeFilterForm" class="row g-2 align-items-center">
                <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">

                <!-- Filter Program Studi -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-graduation-cap text-primary"></i> Program Studi
                    </label>
                    <select name="prodi_id" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('obeFilterForm').submit()">
                        <option value="">-- Semua Program Studi --</option>
                        <?php foreach ($studyPrograms as $sp): ?>
                            <option value="<?= (int)$sp['obe_id'] ?>" <?= ($filterProdiId === (int)$sp['obe_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sp['degree'] . ' ' . $sp['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kurikulum (Hanya kurikulum prodi terpilih jika prodi difilter) -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-book-bookmark text-primary"></i> Kurikulum
                    </label>
                    <select name="kurikulum_id" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('obeFilterForm').submit()">
                        <option value="">-- Semua Kurikulum --</option>
                        <?php 
                        foreach ($curricula as $cur): 
                            if ($filterProdiId !== null && (int)$cur['study_program_id'] !== $filterProdiId) {
                                continue;
                            }
                        ?>
                            <option value="<?= (int)$cur['obe_id'] ?>" <?= ($filterCurriculumId === (int)$cur['obe_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cur['name'] . ' (' . $cur['code'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Box -->
                <div class="col-12 col-md-4 col-lg-4">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-magnifying-glass text-primary"></i> Cari Data
                    </label>
                    <div class="input-group input-group-sm shadow-sm">
                        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery ?? '') ?>" class="form-control" placeholder="Cari nama, kode, dll...">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </div>

                <!-- Reset Filter Button -->
                <?php if ($filterProdiId !== null || $filterCurriculumId !== null || !empty($searchQuery)): ?>
                    <div class="col-12 col-lg-2 d-flex align-items-end pt-lg-4">
                        <a href="<?= $baseUrl ?>/master/obe?section=<?= urlencode($section) ?>" class="btn btn-sm btn-outline-secondary w-100 shadow-sm" title="Reset Filter & Pencarian">
                            <i class="fa-solid fa-rotate-left me-1"></i>Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <?php if ($filterProdiId !== null || $filterCurriculumId !== null || !empty($searchQuery)): ?>
                <div class="d-flex align-items-center gap-2 mt-2 flex-wrap small">
                    <span class="text-muted">Filter aktif:</span>
                    <?php if ($filterProdiId !== null): ?>
                        <?php 
                        $foundP = null;
                        foreach ($studyPrograms as $sp) { if ((int)$sp['obe_id'] === $filterProdiId) { $foundP = $sp; break; } }
                        ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            Prodi: <?= htmlspecialchars($foundP ? ($foundP['degree'] . ' ' . $foundP['name']) : (string)$filterProdiId) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($filterCurriculumId !== null): ?>
                        <?php 
                        $foundC = null;
                        foreach ($curricula as $cur) { if ((int)$cur['obe_id'] === $filterCurriculumId) { $foundC = $cur; break; } }
                        ?>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                            Kurikulum: <?= htmlspecialchars($foundC ? $foundC['name'] : (string)$filterCurriculumId) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($searchQuery)): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            Cari: "<?= htmlspecialchars($searchQuery) ?>"
                        </span>
                    <?php endif; ?>
                    <span class="text-muted ms-auto">Menampilkan <strong><?= count($records) ?></strong> data</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card-body p-0">
        <?php if (empty($health['available'])): ?>
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
                        <th class="ps-4" style="width: 60px;">No</th>
                        <?php foreach ($columns as $label): ?><th><?= htmlspecialchars($label) ?></th><?php endforeach; ?>
                    </tr></thead>
                    <tbody>
                    <?php $no = 1; foreach ($records as $record): ?><tr>
                        <td class="ps-4 text-muted fw-semibold"><?= $no++ ?></td>
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
