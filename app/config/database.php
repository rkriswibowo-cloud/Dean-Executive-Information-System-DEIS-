<?php
/**
 * Database Configuration
 */
return [
    'host'       => getenv('DB_HOST') ?: 'localhost',
    'port'       => (int)(getenv('DB_PORT') ?: 3306),
    'dbname'     => getenv('DB_NAME') ?: 'deis_db',
    'obe_dbname' => getenv('OBE_DB_NAME') ?: 'obe_cpl',
    'username'   => getenv('DB_USERNAME') ?: 'root',
    'password'   => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'rony123',
    // Gunakan akun MySQL khusus dengan privilege SELECT saja di produksi.
    // Fallback ke akun utama dipertahankan untuk lingkungan lokal saat ini.
    'obe_username' => getenv('OBE_DB_USERNAME') ?: (getenv('DB_USERNAME') ?: 'root'),
    'obe_password' => getenv('OBE_DB_PASSWORD') !== false ? getenv('OBE_DB_PASSWORD') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'rony123'),
    'charset'    => 'utf8mb4'
];
