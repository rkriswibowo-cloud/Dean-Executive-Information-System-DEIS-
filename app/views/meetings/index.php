<?php use App\Helpers\FormatHelper; ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-body mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-check text-primary"></i> Rapat & Tata Kelola Digital Fakultas
        </h4>
        <p class="text-muted small mb-0">Manajemen terpusat paket digital rapat: Undangan, Notulensi, Absensi, Materi, Foto Dokumentasi, dan Tracking RTL.</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <a href="<?= $baseUrl ?>/meetings/rtl" class="btn btn-sm btn-soft-info rounded-3 px-3 shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check"></i> Tracking RTL
        </a>
        <a href="<?= $baseUrl ?>/meetings/create" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i> Buat Jadwal Rapat
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-folder-open text-primary"></i> Daftar Arsip & Jadwal Rapat Fakultas
        </h5>
        <span class="badge badge-soft-primary px-3 py-2"><?= count($meetings) ?> Rapat Tercatat</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Nomor & Judul Rapat</th>
                        <th>Jenis Rapat</th>
                        <th>Tanggal & Waktu</th>
                        <th>Tempat</th>
                        <th>Pimpinan Rapat</th>
                        <th>Paket Dokumen</th>
                        <th>RTL</th>
                        <th class="text-end pe-4">Status & Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meetings as $m): ?>
                        <tr>
                            <td class="ps-4" style="max-width: 280px;">
                                <span class="badge badge-soft-secondary font-monospace mb-1"><?= htmlspecialchars($m['meeting_number'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="fw-bold text-body fs-6"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><span class="badge badge-soft-primary"><?= htmlspecialchars($m['type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <div class="fw-semibold text-body"><?= FormatHelper::indonesianDate($m['meeting_date']) ?></div>
                                <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= substr($m['start_time'], 0, 5) ?> WIB</small>
                            </td>
                            <td>
                                <span class="text-body"><i class="fa-solid fa-location-dot text-muted me-1"></i><?= htmlspecialchars($m['location'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="text-body fw-medium"><?= htmlspecialchars($m['chairperson_name'] ?? 'Dekan Fakultas', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="badge badge-soft-info"><i class="fa-solid fa-file-lines me-1"></i><?= $m['document_count'] ?> Berkas</span>
                            </td>
                            <td>
                                <span class="badge badge-soft-warning"><i class="fa-solid fa-list-check me-1"></i><?= $m['rtl_count'] ?> RTL</span>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div><?= FormatHelper::statusBadge($m['status']) ?></div>
                                    <a href="<?= $baseUrl ?>/meetings/detail?id=<?= $m['id'] ?>" class="btn btn-sm btn-soft-primary rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1" title="Buka Paket Digital">
                                        <span>Paket Digital</span> <i class="fa-solid fa-chevron-right fs-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

