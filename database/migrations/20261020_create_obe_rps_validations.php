<?php
use App\Core\Database;

require dirname(__DIR__, 2) . '/tests/bootstrap.php';

$db = Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS obe_rps_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    obe_course_id INT NOT NULL UNIQUE,
    rps_status ENUM('Lengkap', 'Revisi', 'Belum Lengkap') NOT NULL DEFAULT 'Lengkap',
    cpl_status VARCHAR(100) NULL,
    case_method_flag TINYINT(1) NOT NULL DEFAULT 0,
    rps_document_url VARCHAR(255) NULL,
    rps_updated_at DATE NULL,
    rps_notes TEXT NULL,
    validated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_obe_course (obe_course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "obe_rps_validations ready.\n";
