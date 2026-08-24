<?php
use App\Core\Database;

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getConnection();

try {
    $db->exec("ALTER TABLE lecturers ADD COLUMN nip VARCHAR(30) NULL AFTER nidn");
} catch (Exception $e) {}

try {
    $db->exec("ALTER TABLE lecturers ADD COLUMN email VARCHAR(100) NULL AFTER name");
} catch (Exception $e) {}

try {
    $db->exec("ALTER TABLE lecturers ADD COLUMN phone VARCHAR(30) NULL AFTER email");
} catch (Exception $e) {}

echo "Lecturers table schema updated successfully.\n";
