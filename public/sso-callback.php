<?php
session_start();
$ticket = trim((string)($_GET['ticket'] ?? ''));
if ($ticket === '') { header('Location: login'); exit; }
$context = stream_context_create(['http' => [
    'method' => 'POST',
    'timeout' => 3,
    'ignore_errors' => true,
    'header' => "Content-Type: application/x-www-form-urlencoded\r\nX-Client-Secret: change-deis-secret\r\n",
    'content' => http_build_query(['app' => 'deis', 'ticket' => $ticket]),
]]);
$raw = @file_get_contents('http://localhost/platform-auth/exchange', false, $context);
$payload = $raw ? json_decode($raw, true) : null;
if (!is_array($payload) || empty($payload['ok']) || empty($payload['local_user_id'])) {
    $_SESSION['flash_error'] = 'Tiket SSO tidak valid atau telah kedaluwarsa.';
    header('Location: login'); exit;
}
$config = require dirname(__DIR__) . '/app/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$stmt = $pdo->prepare("SELECT u.*, r.name role_name, r.slug role_slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.status='active' LIMIT 1");
$stmt->execute([(int)$payload['local_user_id']]);
$user = $stmt->fetch();
if (!$user) { $_SESSION['flash_error'] = 'Akun lokal DEIS tidak aktif.'; header('Location: login'); exit; }
session_regenerate_id(true);
unset($user['password']);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_data'] = $user;
$_SESSION['active_context_role'] = $user['role_slug'];
unset($_SESSION['user_permissions'], $_SESSION['impersonator_id'], $_SESSION['impersonator_name']);
$pdo->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$user['id']]);
header('Location: dashboard');
exit;
