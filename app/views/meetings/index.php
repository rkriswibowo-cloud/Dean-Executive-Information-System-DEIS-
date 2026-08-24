<?php use App\Helpers\FormatHelper; ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ti ti-calendar-event text-primary fs-2"></i> Rapat & Tata Kelola Digital Fakultas
        </h3>
        <p class="text-muted small mb-0">Manajemen terpusat paket digital rapat: Undangan, Notulensi, Absensi, Materi, Foto, dan Tracking RTL.</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <a href="<?= $baseUrl ?>/meetings/rtl" class="btn btn-outline-primary shadow-sm btn-crud">
            <i class="ti ti-arrow-guide"></i> Tracking RTL
        </a>
        <a href="<?= $baseUrl ?>/meetings/create" class="btn btn-primary shadow-sm btn-crud">
            <i class="ti ti-plus"></i> Buat Jadwal Rapat
        </a>
    </div>
</div>

<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0">Daftar Arsip & Jadwal Rapat Fakultas</h5>
        <span class="badge bg-primary-subtle text-primary"><?= count($meetings) ?> Rapat Tercatat</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                                <span class="badge bg-light text-dark border mb-1"><?= $m['meeting_number'] ?></span>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= $m['type'] ?></span></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= FormatHelper::indonesianDate($m['meeting_date']) ?></div>
                                <small class="text-muted"><?= substr($m['start_time'], 0, 5) ?> WIB</small>
                            </td>
                            <td><?= htmlspecialchars($m['location'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($m['chairperson_name'] ?? 'Dekan Fakultas', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info"><i class="ti ti-files me-1"></i><?= $m['document_count'] ?> Berkas</span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark"><i class="ti ti-arrow-guide me-1"></i><?= $m['rtl_count'] ?> RTL</span>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="table-actions">
                                    <div class="me-2"><?= FormatHelper::statusBadge($m['status']) ?></div>
                                    <a href="<?= $baseUrl ?>/meetings/detail?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary btn-crud-sm" title="Buka Paket Digital">
                                        Paket Digital <i class="ti ti-arrow-right"></i>
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
