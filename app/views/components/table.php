<?php
/**
 * Reusable Table Component Wrapper (DEIS Design System - OBE Aligned)
 * 
 * Usage:
 * <?php 
 *   $table = [
 *       'headers' => ['NIM & Nama', 'Program Studi', 'Angkatan', 'Status', 'Aksi'],
 *       'body'    => $tableHtmlRows,
 *       'empty'   => 'Belum ada data mahasiswa yang terdaftar.',
 *       'class'   => 'table-hover'
 *   ];
 *   require __DIR__ . '/table.php';
 * ?>
 */
$headers = $table['headers'] ?? [];
$body    = $table['body'] ?? '';
$empty   = $table['empty'] ?? 'Tidak ada data untuk ditampilkan.';
$class   = $table['class'] ?? 'table-hover';
?>
<div class="table-responsive">
    <table class="table <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?> align-middle mb-0">
        <?php if (!empty($headers)): ?>
            <thead>
                <tr>
                    <?php foreach ($headers as $index => $header): 
                        $isLast = ($index === count($headers) - 1);
                        $alignClass = $isLast ? 'text-end pe-4' : ($index === 0 ? 'ps-4' : '');
                    ?>
                        <th class="<?= $alignClass ?>"><?= $header ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
        <?php endif; ?>
        <tbody>
            <?php if (!empty(trim($body))): ?>
                <?= $body ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= max(1, count($headers)) ?>" class="text-center py-5 text-muted">
                        <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-inbox fs-2 opacity-50"></i>
                            <div><?= htmlspecialchars($empty, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
