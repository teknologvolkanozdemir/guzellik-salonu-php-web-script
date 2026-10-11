<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$fields = [
 'site_name'=>['Site adı','text'],'tagline'=>['Slogan','text'],'about'=>['Hakkımızda','area'],
 'meta_description'=>['SEO açıklaması (meta description)','area'],'site_url'=>['Site adresi (ör. https://ornek.com, SEO için)','url'],
 'address'=>['Adres','text'],'phone'=>['Telefon','text'],'email'=>['E-posta','email'],'hours'=>['Çalışma saatleri','text'],
 'open_hour'=>['Açılış saati (0-23)','number'],'close_hour'=>['Kapanış saati (1-24)','number'],
 'whatsapp'=>['WhatsApp numarası (ülke koduyla, ör. 905551112233)','text'],
 'instagram'=>['Instagram adresi','url'],'facebook'=>['Facebook adresi','url'],'x'=>['X (Twitter) adresi','url'],
 'youtube'=>['YouTube adresi','url'],'tiktok'=>['TikTok adresi','url'],
 'admin_path'=>['Yönetim paneli adresi (yalnızca harf, rakam, - ve _; 3-40 karakter)','text'],
];
$errors = []; $ok = false; $oldPath = admin_path();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    $new = [];
    foreach ($fields as $k => [$l, $t]) {
        $v = trim((string)($_POST[$k] ?? ''));
        if ($t === 'url' && $v !== '' && !preg_match('#^https?://#i', $v)) $errors[$k] = "$l http:// veya https:// ile başlamalı.";
        if ($t === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[$k] = 'Geçerli e-posta girin.';
        $new[$k] = $v;
    }
    if (!preg_match('/^[A-Za-z0-9_-]{3,40}$/', $new['admin_path'])) $errors['admin_path'] = 'Yönetim adresi 3-40 karakter; harf, rakam, - ve _ olabilir.';
    elseif (is_file(__DIR__ . '/../' . $new['admin_path'] . '.php') || is_dir(__DIR__ . '/../' . $new['admin_path'])) $errors['admin_path'] = 'Bu ad mevcut bir dosya/klasörle çakışıyor.';
    $new['whatsapp'] = preg_replace('/\D+/', '', $new['whatsapp']);
    if (!$errors) {
        $st = db()->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
        foreach ($new as $k => $v) $st->execute([$k, $v]);
        if ($new['admin_path'] !== $oldPath) { header('Location: ../' . $new['admin_path'] . '/ayarlar.php?saved=1'); exit; }
        header('Location: ayarlar.php?saved=1'); exit;
    }
}
render_header('Ayarlar', 'ayarlar.php', true);
if (isset($_GET['saved'])) echo '<div class="alert alert-ok" role="status">Ayarlar kaydedildi.</div>';
error_summary($errors);
$cur = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : array_combine(array_keys($fields), array_map('setting', array_keys($fields)));
?>
<form method="post" action="ayarlar.php" novalidate>
<?= csrf_field() ?>
<?php foreach ($fields as $k => [$l, $t]): ?>
<label for="<?= e($k) ?>"><?= e($l) ?></label>
<?php if ($t === 'area'): ?><textarea id="<?= e($k) ?>" name="<?= e($k) ?>" rows="3"><?= e($cur[$k] ?? '') ?></textarea>
<?php else: ?><input id="<?= e($k) ?>" name="<?= e($k) ?>" type="<?= $t === 'text' ? 'text' : e($t) ?>" value="<?= e($cur[$k] ?? '') ?>"<?= field_attrs($k, $errors) ?>><?php endif; field_error($k, $errors); ?>
<?php endforeach; ?>
<button type="submit">Kaydet</button>
</form>
<?php render_footer(true);
