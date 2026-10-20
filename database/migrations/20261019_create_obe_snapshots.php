<?php
use App\Core\Database;

require dirname(__DIR__, 2) . '/tests/bootstrap.php';

$db = Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS obe_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    snapshot_type VARCHAR(40) NOT NULL DEFAULT 'ACADEMIC_MASTER',
    academic_year_obe_id BIGINT UNSIGNED NULL,
    label VARCHAR(150) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    record_count INT UNSIGNED NOT NULL DEFAULT 0,
    checksum CHAR(64) NOT NULL,
    captured_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_obe_snapshot_type_date (snapshot_type, created_at),
    KEY idx_obe_snapshot_year (academic_year_obe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "obe_snapshots ready.\n";
