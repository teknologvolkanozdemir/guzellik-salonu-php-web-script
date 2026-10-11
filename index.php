<?php
require __DIR__ . '/includes/functions.php';
render_header('Ana Sayfa', 'index.php');
?>
<p class="lead"><?= e(setting('tagline')) ?></p>
<p><?= e(setting('about')) ?></p>
<p><a class="btn" href="randevu.php">Online randevu al</a></p>
<?php $camps = db()->query("SELECT title,description,discount FROM campaigns WHERE active=1 AND (starts_on IS NULL OR starts_on<=CURDATE()) AND (ends_on IS NULL OR ends_on>=CURDATE()) ORDER BY id DESC LIMIT 2")->fetchAll();
if ($camps): ?>
<section aria-labelledby="kamp"><h2 id="kamp">Kampanyalar</h2><ul class="cards">
<?php foreach ($camps as $c): ?><li class="campaign"><h3><?= e($c['title']) ?> <?= e($c['discount']) ?></h3><p><?= e($c['description']) ?></p></li><?php endforeach; ?>
</ul><p><a href="kampanyalar.php">Tüm kampanyalar</a></p></section>
<?php endif; ?>
<h2>Öne çıkan hizmetler</h2>
<ul class="cards">
<?php foreach (db()->query('SELECT name,description,price FROM services WHERE active=1 ORDER BY id LIMIT 3') as $s): ?>
  <li><h3><?= e($s['name']) ?></h3><p><?= e($s['description']) ?></p><p><strong><?= e(money($s['price'])) ?></strong></p></li>
<?php endforeach; ?>
</ul>
<p><a href="hizmetler.php">Tüm hizmetleri gör</a></p>
<?php render_footer();
