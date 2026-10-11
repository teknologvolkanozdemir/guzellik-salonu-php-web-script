<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) db()->prepare('DELETE FROM messages WHERE id=?')->execute([$id]);
    else db()->prepare('UPDATE messages SET is_read=1 WHERE id=?')->execute([$id]);
    header('Location: mesajlar.php'); exit;
}
render_header('Mesajlar', 'mesajlar.php', true);
foreach (db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 200') as $m): ?>
<article class="alert"><h2><?= e($m['subject']) ?><?= $m['is_read'] ? '' : ' (yeni)' ?></h2>
<p><?= e($m['name']) ?> – <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a> – <?= e($m['created_at']) ?></p>
<p><?= nl2br(e($m['body'])) ?></p>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
<?php if (!$m['is_read']): ?><button type="submit">Okundu işaretle</button><?php endif; ?>
<button type="submit" name="delete" value="1" class="danger" onclick="return confirm('Mesaj silinsin mi?')">Sil</button></form></article>
<?php endforeach;
render_footer(true);
