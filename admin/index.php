<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
render_header('Panel', 'index.php', true);
$q = fn($sql) => (int)db()->query($sql)->fetchColumn();
?>
<ul class="cards">
<li><h2>Bekleyen randevu</h2><p><?= $q("SELECT COUNT(*) FROM appointments WHERE status='beklemede'") ?></p></li>
<li><h2>Okunmamış mesaj</h2><p><?= $q("SELECT COUNT(*) FROM messages WHERE is_read=0") ?></p></li>
<li><h2>Aktif kampanya</h2><p><?= $q("SELECT COUNT(*) FROM campaigns WHERE active=1") ?></p></li>
</ul>
<?php render_footer(true);
