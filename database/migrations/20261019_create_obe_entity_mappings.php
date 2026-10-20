<?php
use App\Core\Database;

require dirname(__DIR__, 2) . '/tests/bootstrap.php';

$db = Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS obe_entity_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(40) NOT NULL,
    obe_id BIGINT UNSIGNED NOT NULL,
    deis_id BIGINT UNSIGNED NULL,
    natural_key VARCHAR(191) NULL,
    match_status ENUM('MAPPED','UNMAPPED','CONFLICT') NOT NULL DEFAULT 'UNMAPPED',
    matched_by VARCHAR(50) NULL,
    last_checked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_obe_entity (entity_type, obe_id),
    KEY idx_obe_mapping_deis (entity_type, deis_id),
    KEY idx_obe_mapping_status (entity_type, match_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "obe_entity_mappings ready.\n";
