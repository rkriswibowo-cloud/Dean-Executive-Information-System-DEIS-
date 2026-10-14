<?php use App\Helpers\FormatHelper; use App\Helpers\CsrfHelper; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title fw-bold mb-0 text-body d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-week text-primary"></i> Pengaturan Tahun Akademik
        </h5>
        <span class="badge badge-soft-primary px-3 py-2">Total <?= count($academicYears) ?> Periode</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-auto-paginate="10">
                <thead class="bg-body-secondary bg-opacity-50 text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Tahun Akademik</th>
                        <th>Semester</th>
                        <th>Periode Tanggal</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($academicYears as $ay): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-body"><?= htmlspecialchars($ay['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge badge-soft-secondary font-monospace"><?= htmlspecialchars($ay['semester'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <span class="text-body"><i class="fa-regular fa-calendar text-muted me-1"></i><?= FormatHelper::indonesianDate($ay['start_date']) ?> s/d <?= FormatHelper::indonesianDate($ay['end_date']) ?></span>
                            </td>
                            <td>
                                <?php if ($ay['is_active']): ?>
                                    <span class="badge badge-soft-success px-3 py-1 fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Aktif Berjalan</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if (!$ay['is_active']): ?>
                                    <form action="<?= $baseUrl ?>/master/academic-years" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="set_active_id" value="<?= $ay['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-soft-primary rounded-3 px-3 py-1" onclick="return confirm('Aktifkan tahun akademik ini?');">
                                            Jadikan Aktif
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

