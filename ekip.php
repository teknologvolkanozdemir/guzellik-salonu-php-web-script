<?php
require __DIR__ . '/includes/functions.php';
render_header('Ekibimiz', 'ekip.php');
?>
<ul class="cards">
<?php foreach (db()->query('SELECT name,title,bio FROM staff WHERE active=1 ORDER BY name') as $p): ?>
  <li><h2><?= e($p['name']) ?></h2><p><strong><?= e($p['title']) ?></strong></p><p><?= e($p['bio']) ?></p></li>
<?php endforeach; ?>
</ul>
<?php render_footer();
