<?php
require_once __DIR__ . '/includes/functions.php';
$p = $_GET['p'] ?? '';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (($_GET['f'] ?? '') === '' && substr($path, -1) !== '/' && $p === admin_path()) { header('Location: ' . $path . '/'); exit; }
$f = basename($_GET['f'] ?? '') ?: 'index';
if ($p !== admin_path() || !preg_match('/^[a-z]+$/', $f) || !is_file(__DIR__ . "/admin/$f.php")) {
    http_response_code(404);
    render_header('Sayfa bulunamadı');
    echo '<p>Aradığınız sayfa bulunamadı. <a href="index.php">Ana sayfaya dön</a>.</p>';
    render_footer();
    exit;
}
chdir(__DIR__ . '/admin');
require __DIR__ . "/admin/$f.php";
