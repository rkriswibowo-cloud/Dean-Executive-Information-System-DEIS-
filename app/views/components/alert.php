<?php
/**
 * Reusable Alert Component (DEIS Design System - OBE Aligned)
 * 
 * Usage:
 * <?php 
 *   $alert = [
 *       'type'        => 'success', // 'success', 'danger', 'warning', 'info'
 *       'title'       => 'Berhasil!',
 *       'message'     => 'Data berhasil diperbarui.',
 *       'dismissible' => true
 *   ];
 *   require __DIR__ . '/alert.php';
 * ?>
 */
$type        = $alert['type'] ?? 'info';
$title       = $alert['title'] ?? '';
$message     = $alert['message'] ?? '';
$dismissible = $alert['dismissible'] ?? true;
$class       = $alert['class'] ?? '';

// Map alert types to appropriate fontawesome icons
$icons = [
    'success' => 'fa-solid fa-circle-check text-success fs-5',
    'danger'  => 'fa-solid fa-triangle-exclamation text-danger fs-5',
    'warning' => 'fa-solid fa-circle-exclamation text-warning fs-5',
    'info'    => 'fa-solid fa-circle-info text-info fs-5',
    'primary' => 'fa-solid fa-bell text-primary fs-5',
];

$iconClass = $icons[$type] ?? 'fa-solid fa-circle-info text-info fs-5';
?>
<div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?> <?= $dismissible ? 'alert-dismissible' : '' ?> <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>" role="alert">
    <div class="d-flex align-items-center gap-2 flex-grow-1">
        <i class="<?= $iconClass ?> flex-shrink-0"></i>
        <div>
            <?php if (!empty($title)): ?>
                <div class="alert-heading fw-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    <?php endif; ?>
</div>
