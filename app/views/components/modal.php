<?php
/**
 * Reusable Modal Component (DEIS Design System - OBE Aligned)
 * 
 * Usage:
 * <?php 
 *   $modal = [
 *       'id'          => 'modalConfirmDelete',
 *       'title'       => 'Konfirmasi Penghapusan',
 *       'icon'        => 'fa-solid fa-triangle-exclamation text-danger',
 *       'size'        => 'md', // sm, md, lg, xl
 *       'body'        => '<p>Apakah Anda yakin ingin menghapus data ini?</p>',
 *       'form_action' => $baseUrl . '/users/delete',
 *       'form_method' => 'POST',
 *       'csrf_token'  => $csrfToken ?? '',
 *       'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
 *                         <button type="submit" class="btn btn-danger">Hapus</button>'
 *   ];
 *   require __DIR__ . '/modal.php';
 * ?>
 */
$id          = $modal['id'] ?? 'modalComponent';
$title       = $modal['title'] ?? 'Modal Title';
$icon        = $modal['icon'] ?? '';
$size        = $modal['size'] ?? 'md';
$body        = $modal['body'] ?? '';
$footer      = $modal['footer'] ?? '';
$formAction  = $modal['form_action'] ?? null;
$formMethod  = $modal['form_method'] ?? 'POST';
$csrfToken   = $modal['csrf_token'] ?? null;
$dialogClass = $size === 'sm' ? 'modal-sm' : ($size === 'lg' ? 'modal-lg' : ($size === 'xl' ? 'modal-xl' : ''));
?>
<div class="modal fade" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" tabindex="-1" aria-labelledby="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered <?= $dialogClass ?>">
        <div class="modal-content">
            <?php if ($formAction): ?>
                <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="<?= htmlspecialchars($formMethod, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($csrfToken): ?>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
            <?php endif; ?>

            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($icon)): ?>
                        <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
                    <?php endif; ?>
                    <h5 class="modal-title" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>Label">
                        <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <?= $body ?>
            </div>

            <?php if (!empty($footer)): ?>
                <div class="modal-footer d-flex align-items-center justify-content-end gap-2">
                    <?= $footer ?>
                </div>
            <?php endif; ?>

            <?php if ($formAction): ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
