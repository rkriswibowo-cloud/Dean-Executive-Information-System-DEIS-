<?php
/**
 * views/components/footer.php
 * Minimalist Modern Footer
 */
?>
<footer class="main-footer">
    <div>
        © <?= date('Y') ?> <strong><?= $appConfig['short_name'] ?></strong> - Sistem Informasi Eksekutif Dekan <?= htmlspecialchars($activeFacultyName ?? 'Fakultas') ?>.
    </div>
    <div>
        Versi <?= $appConfig['version'] ?? '1.0.0' ?> • PHP Native MVC
    </div>
</footer>
