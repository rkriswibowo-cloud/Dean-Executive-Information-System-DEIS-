<?php
use App\Helpers\FormatHelper;
use App\Helpers\CsrfHelper;

$totalIndicators = count($indicators);
$activeIndicators = count(array_filter($indicators, fn($i) => (int)($i['is_active'] ?? 1) === 1));
$ikuCount = count(array_filter($indicators, fn($i) => ($i['category'] ?? '') === 'IKU'));
$renstraCount = count(array_filter($indicators, fn($i) => in_array($i['category'] ?? '', ['Renstra', 'Fakultas', 'SPMI'])));
?>

<!-- 1. Header & Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100">
            <span class="text-muted small fw-semibold">Total Indikator</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $totalIndicators ?> <span class="fs-6 fw-normal text-muted">Item</span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100">
            <span class="text-muted small fw-semibold">Indikator Aktif</span>
            <h3 class="fw-bold mb-0 mt-1 text-success"><?= $activeIndicators ?> <span class="fs-6 fw-normal text-muted">/ <?= $totalIndicators ?></span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100">
            <span class="text-muted small fw-semibold">Kategori IKU Nasional</span>
            <h3 class="fw-bold mb-0 mt-1 text-info"><?= $ikuCount ?> <span class="fs-6 fw-normal text-muted">IKU</span></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100">
            <span class="text-muted small fw-semibold">Renstra, Fak & SPMI</span>
            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $renstraCount ?> <span class="fs-6 fw-normal text-muted">Indikator</span></h3>
        </div>
    </div>
</div>

<!-- 2. Main Master IKU Card & Table -->
<div class="card card-lg shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-body border-bottom py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0">Master Indikator Kinerja Strategis (IKU & Renstra)</h5>
            <small class="text-muted">Kelola data master indikator, formula, target tahunan 2026, dan sumber data integrasi.</small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= $baseUrl ?>/strategic" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="ti ti-arrow-left"></i> Realisasi IKU
            </a>
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddIndicator">
                <i class="ti ti-plus"></i> Tambah Indikator
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 100px;">Kode</th>
                        <th>Nama Indikator Strategis</th>
                        <th style="width: 110px;">Kategori</th>
                        <th style="width: 130px;">Target (2026)</th>
                        <th>Formula & Sumber Data</th>
                        <th style="width: 100px;">Status</th>
                        <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($indicators)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-chart-dots fs-1 mb-2 d-block opacity-50"></i>
                                Belum ada data indikator strategis. Klik tombol <strong>Tambah Indikator</strong> di atas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($indicators as $ind): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold px-2 py-1">
                                        <?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="text-muted">Satuan: <strong><?= htmlspecialchars($ind['unit'] ?? '%', ENT_QUOTES, 'UTF-8') ?></strong></small>
                                </td>
                                <td>
                                    <?php
                                    $catClass = match($ind['category'] ?? 'IKU') {
                                        'IKU'      => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'Renstra'  => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'SPMI'     => 'bg-info-subtle text-info border border-info-subtle',
                                        default    => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                    };
                                    ?>
                                    <span class="badge <?= $catClass ?>"><?= htmlspecialchars($ind['category'] ?? 'IKU', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <strong class="text-dark fs-6"><?= $ind['target_value'] ?? '-' ?></strong> 
                                    <span class="small text-muted"><?= htmlspecialchars($ind['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <div class="small text-dark fw-semibold mb-0.5" style="max-width: 320px;">
                                        <?= htmlspecialchars($ind['formula'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="small text-muted d-flex align-items-center gap-1">
                                        <i class="ti ti-database fs-6"></i>
                                        <span>Sumber: <?= htmlspecialchars($ind['data_source'] ?? 'Input Manual / SIM', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ((int)($ind['is_active'] ?? 1) === 1): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-check" style="font-size: 0.75rem;"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-x" style="font-size: 0.75rem;"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="table-actions d-inline-flex gap-1 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalEditIndicator<?= $ind['id'] ?>" title="Edit Indikator">
                                            <i class="ti ti-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-crud-sm" data-bs-toggle="modal" data-bs-target="#modalDeleteIndicator<?= $ind['id'] ?>" title="Hapus Indikator">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Edit Indikator -->
                                    <div class="modal fade" id="modalEditIndicator<?= $ind['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow text-start">
                                                <form action="<?= $baseUrl ?>/strategic/indicators/update" method="POST">
                                                    <?= CsrfHelper::tokenField() ?>
                                                    <input type="hidden" name="id" value="<?= $ind['id'] ?>">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                                                            <i class="ti ti-edit"></i> Edit Indikator: <?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold">Kode Indikator <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?>" required placeholder="Misal: IKU-1">
                                                            </div>
                                                            <div class="col-md-8">
                                                                <label class="form-label small fw-bold">Nama Indikator <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?>" required placeholder="Nama indikator strategis...">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold">Kategori</label>
                                                                <select class="form-select" name="category">
                                                                    <option value="IKU" <?= ($ind['category'] ?? '') === 'IKU' ? 'selected' : '' ?>>IKU Nasional</option>
                                                                    <option value="Renstra" <?= ($ind['category'] ?? '') === 'Renstra' ? 'selected' : '' ?>>Renstra</option>
                                                                    <option value="Fakultas" <?= ($ind['category'] ?? '') === 'Fakultas' ? 'selected' : '' ?>>Fakultas</option>
                                                                    <option value="SPMI" <?= ($ind['category'] ?? '') === 'SPMI' ? 'selected' : '' ?>>Standar SPMI</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold">Satuan Target</label>
                                                                <input type="text" class="form-control" name="unit" value="<?= htmlspecialchars($ind['unit'] ?? '%', ENT_QUOTES, 'UTF-8') ?>" placeholder="%, Skor, Orang, dll">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold">Target Tahun 2026 <span class="text-danger">*</span></label>
                                                                <input type="number" step="0.01" class="form-control" name="target_value" value="<?= $ind['target_value'] ?? 100 ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Sumber Data Integrasi</label>
                                                                <input type="text" class="form-control" name="data_source" value="<?= htmlspecialchars($ind['data_source'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Misal: Tracer Study, SIM BKD, PDDIKTI">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Status Indikator</label>
                                                                <select class="form-select" name="is_active">
                                                                    <option value="1" <?= (int)($ind['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif (Dipantau)</option>
                                                                    <option value="0" <?= (int)($ind['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nonaktif</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold">Formula / Rumus Perhitungan</label>
                                                                <textarea class="form-control" name="formula" rows="3" placeholder="Jelaskan cara perhitungan atau pembagi..."><?= htmlspecialchars($ind['formula'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Hapus Indikator -->
                                    <div class="modal fade" id="modalDeleteIndicator<?= $ind['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow text-start">
                                                <form action="<?= $baseUrl ?>/strategic/indicators/delete" method="POST">
                                                    <?= CsrfHelper::tokenField() ?>
                                                    <input type="hidden" name="id" value="<?= $ind['id'] ?>">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                                                            <i class="ti ti-alert-triangle"></i> Konfirmasi Hapus Indikator
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-center">
                                                        <i class="ti ti-trash fs-1 text-danger mb-2 d-block"></i>
                                                        <h6 class="fw-bold mb-1">Hapus <?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?>?</h6>
                                                        <p class="text-muted small mb-0">
                                                            Anda akan menghapus indikator <strong>"<?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?>"</strong> beserta seluruh data target dan histori realisasinya.
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer bg-light justify-content-center">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Hapus Sekarang</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Indikator Baru -->
<div class="modal fade" id="modalAddIndicator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow text-start">
            <form action="<?= $baseUrl ?>/strategic/indicators/create" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                        <i class="ti ti-plus"></i> Tambah Master Indikator Kinerja Strategis
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Kode Indikator <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required placeholder="Contoh: IKU-9, RENSTRA-4">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Nama Indikator <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Contoh: Persentase Dosen Praktisi Industri...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Kategori</label>
                            <select class="form-select" name="category">
                                <option value="IKU" selected>IKU Nasional</option>
                                <option value="Renstra">Renstra Fakultas</option>
                                <option value="Fakultas">Indikator Khusus Fakultas</option>
                                <option value="SPMI">Standar Mutu SPMI</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Satuan Target</label>
                            <input type="text" class="form-control" name="unit" value="%" placeholder="%, Skor, Orang, Judul, Rp">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Target Tahun 2026 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="target_value" value="80.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sumber Data Integrasi</label>
                            <input type="text" class="form-control" name="data_source" placeholder="Contoh: SIM BKD, Tracer Study, Lab Center">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status Indikator</label>
                            <select class="form-select" name="is_active">
                                <option value="1" selected>Aktif (Dipantau)</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Formula / Rumus Perhitungan</label>
                            <textarea class="form-control" name="formula" rows="3" placeholder="Tuliskan formula atau rasio penghitungan indikator..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Indikator Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
