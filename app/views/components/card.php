<?php
/**
 * Reusable Card Component (DEIS Design System - OBE Aligned)
 * 
 * Usage:
 * <?php 
 *   $card = [
 *       'type'     => 'kpi', // 'kpi' or 'default'
 *       'title'    => 'Total Mahasiswa',
 *       'value'    => '1.240',
 *       'icon'     => 'fa-solid fa-user-graduate',
 *       'badge'    => '+5.2%',
 *       'subtitle' => 'Tahun Akademik 2026/2027'
 *   ];
 *   require __DIR__ . '/card.php';
 * ?>
 */
$type     = $card['type'] ?? 'default';
$title    = $card['title'] ?? '';
$subtitle = $card['subtitle'] ?? '';
$class    = $card['class'] ?? '';
$actions  = $card['actions'] ?? '';
$body     = $card['body'] ?? '';
$footer   = $card['footer'] ?? '';

if ($type === 'kpi'):
    $value = $card['value'] ?? '0';
    $icon  = $card['icon'] ?? 'fa-solid fa-chart-line';
    $badge = $card['badge'] ?? '';
?>
    <div class="kpi-card <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>">
        <div style="flex: 1; min-width: 0;">
            <div class="kpi-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <span class="kpi-value"><?= $value ?></span>
                <?php if (!empty($badge)): ?>
                    <span class="badge bg-primary-subtle"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($subtitle)): ?>
                <div class="text-muted small mt-1" style="font-size: 0.8rem;"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>
        <div class="kpi-icon-wrap flex-shrink-0">
            <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
        </div>
    </div>
<?php else: ?>
    <div class="card <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($title) || !empty($actions)): ?>
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h5>
                    <?php if (!empty($subtitle)): ?>
                        <div class="card-subtitle"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($actions)): ?>
                    <div class="card-actions d-flex align-items-center gap-2">
                        <?= $actions ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <?= $body ?>
        </div>
        <?php if (!empty($footer)): ?>
            <div class="card-footer">
                <?= $footer ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
