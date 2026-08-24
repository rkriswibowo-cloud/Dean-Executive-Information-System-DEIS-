<?php use App\Helpers\FormatHelper; use App\Helpers\CsrfHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0">Pengaturan Tahun Akademik</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
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
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($ay['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $ay['semester'] ?></span></td>
                            <td><?= FormatHelper::indonesianDate($ay['start_date']) ?> s/d <?= FormatHelper::indonesianDate($ay['end_date']) ?></td>
                            <td>
                                <?php if ($ay['is_active']): ?>
                                    <span class="badge bg-success text-white px-3 py-1"><i class="ti ti-check me-1"></i>Aktif Berjalan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if (!$ay['is_active']): ?>
                                    <form action="<?= $baseUrl ?>/master/academic-years" method="POST" class="d-inline">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="set_active_id" value="<?= $ay['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Aktifkan tahun akademik ini?');">
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
