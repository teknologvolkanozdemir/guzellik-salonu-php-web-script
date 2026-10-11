<?php
session_start();
require_once __DIR__ . '/db.php';

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function setting(string $key): string {
    static $all = null;
    if ($all === null) {
        $all = db()->query('SELECT `key`,`value` FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $all[$key] ?? '';
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): bool {
    return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
}

function money($v): string { return number_format((float)$v, 2, ',', '.') . ' ₺'; }

function require_admin(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}

function site_url(): string {
    $u = rtrim(setting('site_url'), '/');
    if ($u !== '') return $u;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir;
}

function whatsapp_link(string $text): string {
    $n = preg_replace('/\D+/', '', setting('whatsapp'));
    return $n === '' ? '' : 'https://wa.me/' . $n . '?text=' . rawurlencode($text);
}

function social_links(): array {
    $out = [];
    foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X (Twitter)', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $k => $label) {
        $u = setting($k);
        if ($u !== '' && preg_match('#^https?://#i', $u)) $out[$label] = $u;
    }
    return $out;
}

function admin_path(): string {
    $p = setting('admin_path');
    return preg_match('/^[A-Za-z0-9_-]{3,40}$/', $p) ? $p : 'yonetim';
}

function render_header(string $title, string $current = '', bool $admin = false, string $desc = ''): void {
    $site = setting('site_name');
    $nav = $admin ? [
        'index.php' => 'Panel', 'randevular.php' => 'Randevular', 'hizmetler.php' => 'Hizmetler',
        'personel.php' => 'Personel', 'kampanyalar.php' => 'Kampanyalar', 'mesajlar.php' => 'Mesajlar', 'ayarlar.php' => 'Ayarlar', 'logout.php' => 'Çıkış',
    ] : [
        'index.php' => 'Ana Sayfa', 'hizmetler.php' => 'Hizmetler', 'kampanyalar.php' => 'Kampanyalar', 'ekip.php' => 'Ekibimiz',
        'randevu.php' => 'Randevu Al', 'iletisim.php' => 'İletişim',
    ];
    $css = $admin ? '../assets/css/style.css' : 'assets/css/style.css';
    ?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> – <?= e($site) ?><?= $admin ? ' Yönetim' : '' ?></title>
<?php if ($admin): ?>
<meta name="robots" content="noindex,nofollow">
<?php else:
    $d = $desc !== '' ? $desc : (setting('meta_description') ?: setting('tagline'));
    $canon = site_url() . '/' . ($current === 'index.php' ? '' : $current);
?>
<meta name="description" content="<?= e($d) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:title" content="<?= e($title) ?> – <?= e($site) ?>">
<meta property="og:description" content="<?= e($d) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta name="twitter:card" content="summary">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'BeautySalon', 'name' => $site,
    'description' => setting('meta_description'), 'url' => site_url() . '/',
    'telephone' => setting('phone'), 'email' => setting('email'),
    'address' => setting('address'), 'openingHours' => setting('hours'),
    'sameAs' => array_values(social_links()),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endif; ?>
<link rel="stylesheet" href="<?= e($css) ?>">
</head>
<body>
<a class="skip-link" href="#main">Ana içeriğe geç</a>
<header class="site-header">
  <p class="brand"><?= e($site) ?><?= $admin ? ' – Yönetim' : '' ?></p>
  <nav aria-label="Ana menü">
    <ul>
    <?php foreach ($nav as $file => $label): ?>
      <li><a href="<?= e($file) ?>"<?= $current === $file ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
    <?php endforeach; ?>
    </ul>
  </nav>
</header>
<main id="main" tabindex="-1">
<h1><?= e($title) ?></h1>
<?php
}

function render_footer(bool $admin = false): void {
    $wa = whatsapp_link('Merhaba, bilgi almak istiyorum.');
    $war = whatsapp_link('Merhaba, randevu almak istiyorum.');
    ?>
</main>
<?php if (!$admin): ?>
<?php if ($wa): ?>
<div class="wa-bar" role="region" aria-label="WhatsApp ile hızlı iletişim">
  <a class="btn wa" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer">WhatsApp ile iletişim<span class="sr-only"> (yeni sekmede açılır)</span></a>
  <a class="btn wa" href="<?= e($war) ?>" target="_blank" rel="noopener noreferrer">WhatsApp ile randevu al<span class="sr-only"> (yeni sekmede açılır)</span></a>
</div>
<?php endif; ?>
<footer class="site-footer">
  <address>
    <?= e(setting('address')) ?><br>
    Telefon: <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a><br>
    E-posta: <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>
  </address>
  <p>Çalışma saatleri: <?= e(setting('hours')) ?></p>
  <?php if ($soc = social_links()): ?>
  <nav aria-label="Sosyal medya"><ul class="social">
    <?php foreach ($soc as $label => $url): ?><li><a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?><span class="sr-only"> (yeni sekmede açılır)</span></a></li><?php endforeach; ?>
  </ul></nav>
  <?php endif; ?>
</footer>
<?php endif; ?>
</body>
</html>
<?php
}

/** Hata özeti ve alan hatası için erişilebilir yardımcılar */
function error_summary(array $errors): void {
    if (!$errors) return;
    echo '<div class="alert alert-error" role="alert" tabindex="-1" id="errors"><p>Formda hatalar var:</p><ul>';
    foreach ($errors as $f => $m) echo '<li><a href="#' . e($f) . '">' . e($m) . '</a></li>';
    echo '</ul></div>';
}
function field_attrs(string $name, array $errors): string {
    return isset($errors[$name]) ? ' aria-invalid="true" aria-describedby="' . e($name) . '-err"' : '';
}
function field_error(string $name, array $errors): void {
    if (isset($errors[$name])) echo '<span class="error-text" id="' . e($name) . '-err">' . e($errors[$name]) . '</span>';
}
