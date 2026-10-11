<?php
require_once __DIR__ . '/../includes/functions.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    $st = db()->prepare('SELECT id,password_hash FROM admins WHERE username=?');
    $st->execute([trim($_POST['username'] ?? '')]);
    $a = $st->fetch();
    if ($a && password_verify($_POST['password'] ?? '', $a['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $a['id'];
        header('Location: index.php'); exit;
    }
    $err = 'Kullanıcı adı veya parola hatalı.';
}
render_header('Yönetici Girişi', '', true);
if ($err) echo '<div class="alert alert-error" role="alert">' . e($err) . '</div>';
?>
<form method="post" action="login.php">
<?= csrf_field() ?>
<label for="username">Kullanıcı adı</label>
<input id="username" name="username" autocomplete="username" required>
<label for="password">Parola</label>
<input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="submit">Giriş yap</button>
</form>
<?php render_footer(true);
