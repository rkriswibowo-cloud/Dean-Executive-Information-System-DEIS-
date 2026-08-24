<?php use App\Helpers\FormatHelper; ?>
<div class="card card-lg shadow-sm border-0 rounded-4">
    <div class="card-header bg-body border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="card-title fw-bold mb-0">Master Indikator Kinerja Strategis (Dinamis)</h5>
            <small class="text-muted">Struktur indikator berbasis data dinamis sesuai prinsip tata kelola database (PRD Bagian 38).</small>
        </div>
        <a href="<?= $baseUrl ?>/strategic" class="btn btn-sm btn-outline-secondary">← Realisasi IKU</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Indikator</th>
                        <th>Kategori</th>
                        <th>Formula Perhitungan</th>
                        <th>Satuan</th>
                        <th>Sumber Data</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($indicators as $ind): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($ind['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($ind['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $ind['category'] ?></span></td>
                            <td style="max-width: 260px;"><small class="text-muted"><?= htmlspecialchars($ind['formula'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td><strong><?= $ind['unit'] ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($ind['data_source'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td class="text-end pe-4">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
