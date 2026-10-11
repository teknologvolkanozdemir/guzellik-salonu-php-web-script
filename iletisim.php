<?php
require __DIR__ . '/includes/functions.php';
$errors = []; $ok = false;
$v = ['name'=>'','email'=>'','subject'=>'','body'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    if ($v['name'] === '') $errors['name'] = 'Lütfen adınızı girin.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    if ($v['subject'] === '') $errors['subject'] = 'Lütfen konu girin.';
    if ($v['body'] === '') $errors['body'] = 'Lütfen mesajınızı yazın.';
    if (!$errors) {
        db()->prepare('INSERT INTO messages (name,email,subject,body) VALUES (?,?,?,?)')
            ->execute([mb_substr($v['name'],0,100), mb_substr($v['email'],0,120), mb_substr($v['subject'],0,150), $v['body']]);
        $ok = true; $v = array_map(fn($x) => '', $v);
    }
}
render_header('İletişim', 'iletisim.php');
if ($ok) echo '<div class="alert alert-ok" role="status">Mesajınız alındı, teşekkür ederiz.</div>';
error_summary($errors);
?>
<p>Adres: <?= e(setting('address')) ?><br>Çalışma saatleri: <?= e(setting('hours')) ?></p>
<form method="post" action="iletisim.php" novalidate>
<?= csrf_field() ?>
<p class="hint">Tüm alanlar zorunludur.</p>
<label for="name">Adınız</label>
<input id="name" name="name" autocomplete="name" required value="<?= e($v['name']) ?>"<?= field_attrs('name',$errors) ?>><?php field_error('name',$errors); ?>
<label for="email">E-posta</label>
<input id="email" name="email" type="email" autocomplete="email" required value="<?= e($v['email']) ?>"<?= field_attrs('email',$errors) ?>><?php field_error('email',$errors); ?>
<label for="subject">Konu</label>
<input id="subject" name="subject" required value="<?= e($v['subject']) ?>"<?= field_attrs('subject',$errors) ?>><?php field_error('subject',$errors); ?>
<label for="body">Mesaj</label>
<textarea id="body" name="body" rows="6" required<?= field_attrs('body',$errors) ?>><?= e($v['body']) ?></textarea><?php field_error('body',$errors); ?>
<button type="submit">Gönder</button>
</form>
<?php if ($errors): ?><script>document.getElementById('errors').focus();</script><?php endif;
render_footer();
