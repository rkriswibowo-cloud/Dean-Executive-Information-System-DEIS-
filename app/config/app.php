<?php
/**
 * Application Configuration
 */

// Auto-detect base URL (termasuk reverse proxy / Cloudflare Tunnel)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
$protocol = $isHttps ? "https://" : "http://";
// Auto-detect host (utamakan X-Forwarded-Host dari Cloudflare Tunnel / proxy)
$rawHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($rawHost, ',') !== false) {
    $rawHost = trim(explode(',', $rawHost)[0]);
}
$host = $rawHost;

$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$detectedBaseUrl = $protocol . $host . rtrim($scriptName, '/');

// Resolusi base_url:
// Otomatis ikuti domain yang diakses browser/tunnel, kecuali APP_URL di-set eksplisit ke domain publik
$envAppUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') : '';
if (!empty($envAppUrl) && strpos($envAppUrl, 'localhost') === false) {
    $baseUrl = $envAppUrl;
} elseif (empty($_SERVER['HTTP_HOST']) && !empty($envAppUrl)) {
    $baseUrl = $envAppUrl;
} else {
    $baseUrl = $detectedBaseUrl;
}

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
