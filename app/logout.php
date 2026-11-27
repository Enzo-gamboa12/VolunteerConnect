<?php
require_once __DIR__ . '/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: index.php'); exit; }
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires'=>time()-42000, 'path'=>$p['path'], 'domain'=>$p['domain'], 'secure'=>$p['secure'], 'httponly'=>$p['httponly'], 'samesite'=>$p['samesite'] ?? 'Lax']);
}
session_destroy(); header('Location: login.php', true, 303); exit;
