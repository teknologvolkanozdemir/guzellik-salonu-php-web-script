<?php
require __DIR__ . '/includes/functions.php';
render_header('Hizmetler ve Fiyatlar', 'hizmetler.php');
$rows = db()->query('SELECT * FROM services WHERE active=1 ORDER BY category,name')->fetchAll();
$by = [];
foreach ($rows as $r) $by[$r['category']][] = $r;
foreach ($by as $cat => $list): ?>
<section aria-labelledby="c-<?= e(md5($cat)) ?>">
<h2 id="c-<?= e(md5($cat)) ?>"><?= e($cat) ?></h2>
<div class="table-wrap"><table>
<caption class="sr-only"><?= e($cat) ?> hizmetleri</caption>
<thead><tr><th scope="col">Hizmet</th><th scope="col">Açıklama</th><th scope="col">Süre</th><th scope="col">Fiyat</th></tr></thead>
<tbody>
<?php foreach ($list as $s): ?>
<tr><th scope="row"><?= e($s['name']) ?></th><td><?= e($s['description']) ?></td><td><?= (int)$s['duration_min'] ?> dk</td><td><?= e(money($s['price'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
</section>
<?php endforeach;
if (!$by) echo '<p>Henüz hizmet eklenmedi.</p>'; ?>
<p><a class="btn" href="randevu.php">Randevu al</a></p>
<?php render_footer();
