<?php
/**
 * Application Configuration
 */

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Normalize script base directory
$baseUrl = $protocol . $host . rtrim($scriptName, '/');
if (substr($baseUrl, -7) === '/public') {
    // If running with public in URL, baseUrl points to public
    $rootUrl = substr($baseUrl, 0, -7);
} else {
    $rootUrl = $baseUrl;
}

return [
    'name'       => 'Dean Executive Information System (DEIS)',
    'short_name' => 'DEIS',
    'version'    => '1.0.0',
    'env'        => 'development',
    'timezone'   => 'Asia/Jakarta',
    'base_url'   => $baseUrl,
    'root_url'   => $rootUrl,
    'session'    => [
        'name'     => 'DEIS_SESSION',
        'lifetime' => 7200, // 2 hours
    ]
];
