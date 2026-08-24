<?php
/**
 * DEIS - Database Migration & Seeder Script with detailed query logging
 */

$host = 'localhost';
$port = '3306';
$username = 'root';
$password = '';
$dbname = 'deis_db';

echo "========================================================\n";
echo "   DEAN EXECUTIVE INFORMATION SYSTEM (DEIS) MIGRATION   \n";
echo "========================================================\n\n";

try {
    echo "[1/4] Connecting to MySQL server at {$host}:{$port}...\n";
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "      -> Connected successfully!\n\n";

    echo "[2/4] Creating database `{$dbname}` if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");
    echo "      -> Database `{$dbname}` ready!\n\n";

    echo "[3/4] Importing Schema (database/schema.sql)...\n";
    $schemaFile = __DIR__ . '/schema.sql';
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "      -> Schema tables created successfully!\n\n";

    echo "[4/4] Importing Initial Seeds (database/seeds.sql)...\n";
    $seedsFile = __DIR__ . '/seeds.sql';
    $seedsSql = file_get_contents($seedsFile);
    
    // Split queries by semicolon to catch exact error if any
    $queries = explode(";\n", $seedsSql);
    foreach ($queries as $idx => $q) {
        $q = trim($q);
        if ($q !== '') {
            try {
                $pdo->exec($q);
            } catch (Exception $qe) {
                echo "\n[ERROR] Query " . ($idx + 1) . " Failed:\n";
                echo substr($q, 0, 200) . "...\n";
                echo "Error: " . $qe->getMessage() . "\n";
                exit(1);
            }
        }
    }
    echo "      -> Seed data imported successfully!\n\n";

    echo "========================================================\n";
    echo " MIGRATION COMPLETED SUCCESSFULLY!\n";
    echo " Default Logins:\n";
    echo " - Dekan:       username: dekan       password: password\n";
    echo " - Admin:       username: admin       password: password\n";
    echo " - Kaprodi TI:  username: kaprodi.ti  password: password\n";
    echo " - SPMI/GKM:    username: spmi        password: password\n";
    echo " - Operator:    username: operator    password: password\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
