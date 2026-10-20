<?php
/**
 * Database Configuration
 */
return [
    'host'       => 'localhost',
    'port'       => 3306,
    'dbname'     => 'deis_db',
    'obe_dbname' => 'obe_cpl',
    'username'   => 'root',
    'password'   => '',
    // Gunakan akun MySQL khusus dengan privilege SELECT saja di produksi.
    // Fallback ke akun utama dipertahankan untuk lingkungan lokal saat ini.
    'obe_username' => getenv('OBE_DB_USERNAME') ?: 'root',
    'obe_password' => getenv('OBE_DB_PASSWORD') !== false ? getenv('OBE_DB_PASSWORD') : '',
    'charset'    => 'utf8mb4'
];
