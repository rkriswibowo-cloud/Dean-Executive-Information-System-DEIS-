<?php
require __DIR__ . '/bootstrap.php';

$failures = [];
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) $failures[] = $message;
};

$sidebar = file_get_contents(dirname(__DIR__) . '/app/views/components/sidebar.php');
$layout = file_get_contents(dirname(__DIR__) . '/app/views/layouts/main.php');
$css = file_get_contents(dirname(__DIR__) . '/public/assets/css/app-theme.css');

$assert(str_contains($sidebar, 'toggleAllGroupsBtn'), 'Tombol ciutkan semua belum tersedia.');
$assert(str_contains($sidebar, 'Ciutkan Semua'), 'Label awal ciutkan semua belum tersedia.');
$assert(str_contains($sidebar, 'sidebarFooterCollapseBtn'), 'Tombol ciutkan lebar sidebar belum memiliki ID standar.');
$assert(str_contains($layout, 'window.toggleAllSidebarGroups'), 'Fungsi ciutkan semua belum tersedia.');
$assert(str_contains($layout, "'Bentangkan Semua'"), 'Label bentangkan semua belum tersedia.');
$assert(str_contains($layout, 'deis_sidebar_group_states_'), 'Status kelompok menu belum disimpan per role.');
$assert(str_contains($css, '.sidebar-mini-action'), 'Tampilan tombol ciutkan belum tersedia.');
$assert(str_contains($css, '.sidebar-collapsed .sidebar-action-bar'), 'Kontrol belum menyesuaikan mode sidebar mini.');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ({$checks} checks)\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK ({$checks} checks) - sidebar group and width collapse controls are available.\n";
