<?php
require __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach (['', 'hizmetler.php', 'kampanyalar.php', 'ekip.php', 'randevu.php', 'iletisim.php'] as $u): ?>
<url><loc><?= e(site_url() . '/' . $u) ?></loc></url>
<?php endforeach; ?>
</urlset>
