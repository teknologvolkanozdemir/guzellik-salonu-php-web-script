<?php
require __DIR__ . '/includes/functions.php';
render_header('Kampanyalar', 'kampanyalar.php', false, 'Güncel kampanya ve indirimlerimiz.');
$rows = db()->query("SELECT * FROM campaigns WHERE active=1 AND (starts_on IS NULL OR starts_on<=CURDATE()) AND (ends_on IS NULL OR ends_on>=CURDATE()) ORDER BY id DESC")->fetchAll();
if (!$rows) echo '<p>Şu anda aktif kampanya bulunmuyor.</p>'; ?>
<ul class="cards">
<?php foreach ($rows as $c): ?>
<li class="campaign"><h2><?= e($c['title']) ?></h2>
<?php if ($c['discount']): ?><p><strong>İndirim: <?= e($c['discount']) ?></strong></p><?php endif; ?>
<p><?= e($c['description']) ?></p>
<?php if ($c['ends_on']): ?><p>Son gün: <time datetime="<?= e($c['ends_on']) ?>"><?= e(date('d.m.Y', strtotime($c['ends_on']))) ?></time></p><?php endif; ?></li>
<?php endforeach; ?>
</ul>
<p><a class="btn" href="randevu.php">Randevu al</a></p>
<?php render_footer();
