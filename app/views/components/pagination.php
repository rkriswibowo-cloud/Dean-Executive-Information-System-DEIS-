<?php
/**
 * Reusable Pagination Component (DEIS Design System - OBE Aligned)
 * 
 * Usage:
 * <?php 
 *   $pagination = [
 *       'current_page' => 1,
 *       'total_pages'  => 10,
 *       'base_url'     => $baseUrl . '/students',
 *       'query_params' => ['search' => $search ?? '', 'status' => $status ?? '']
 *   ];
 *   require __DIR__ . '/pagination.php';
 * ?>
 */
$currentPage = (int)($pagination['current_page'] ?? 1);
$totalPages  = (int)($pagination['total_pages'] ?? 1);
$baseUrl     = $pagination['base_url'] ?? '';
$queryParams = $pagination['query_params'] ?? [];

if ($totalPages <= 1) {
    return;
}

$buildPageUrl = function($page) use ($baseUrl, $queryParams) {
    $params = array_merge($queryParams, ['page' => $page]);
    // remove empty parameters
    $filtered = array_filter($params, fn($v) => $v !== '' && $v !== null);
    $query = http_build_query($filtered);
    return $baseUrl . ($query ? '?' . $query : '');
};

// Window of pages to display
$range = 2;
$startPage = max(1, $currentPage - $range);
$endPage   = min($totalPages, $currentPage + $range);
?>
<div class="table-pagination-wrapper">
    <div class="table-pagination-info">
        <span>Halaman <strong class="page-start text-body fw-semibold"><?= $currentPage ?></strong> dari <strong class="page-total text-body fw-semibold"><?= $totalPages ?></strong></span>
    </div>

    <nav aria-label="Navigasi Halaman" class="table-pagination-nav">
        <ul class="pagination pagination-sm deis-pagination pagination-nav mb-0">
            <!-- First Button (If total pages > 5) -->
            <?php if ($totalPages > 5): ?>
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($currentPage > 1) ? htmlspecialchars($buildPageUrl(1), ENT_QUOTES, 'UTF-8') : '#' ?>" title="Halaman Pertama" aria-label="Pertama">
                        <i class="fa-solid fa-angles-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Previous Button -->
            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= ($currentPage > 1) ? htmlspecialchars($buildPageUrl($currentPage - 1), ENT_QUOTES, 'UTF-8') : '#' ?>" title="Sebelumnya" aria-label="Sebelumnya">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            </li>

            <!-- First Page Link if out of view -->
            <?php if ($startPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($buildPageUrl(1), ENT_QUOTES, 'UTF-8') ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled"><span class="page-link dots">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Numbered Page Links -->
            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                <li class="page-item <?= ($p === $currentPage) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($buildPageUrl($p), ENT_QUOTES, 'UTF-8') ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>

            <!-- Last Page Link if out of view -->
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled"><span class="page-link dots">…</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($buildPageUrl($totalPages), ENT_QUOTES, 'UTF-8') ?>"><?= $totalPages ?></a>
                </li>
            <?php endif; ?>

            <!-- Next Button -->
            <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= ($currentPage < $totalPages) ? htmlspecialchars($buildPageUrl($currentPage + 1), ENT_QUOTES, 'UTF-8') : '#' ?>" title="Berikutnya" aria-label="Berikutnya">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </li>

            <!-- Last Button (If total pages > 5) -->
            <?php if ($totalPages > 5): ?>
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($currentPage < $totalPages) ? htmlspecialchars($buildPageUrl($totalPages), ENT_QUOTES, 'UTF-8') : '#' ?>" title="Halaman Terakhir" aria-label="Terakhir">
                        <i class="fa-solid fa-angles-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
