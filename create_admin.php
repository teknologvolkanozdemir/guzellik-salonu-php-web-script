<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if ($argc < 3) { fwrite(STDERR, "Kullanım: php create_admin.php kullanici sifre\n"); exit(1); }
require __DIR__ . '/includes/db.php';
$st = db()->prepare('INSERT INTO admins (username,password_hash) VALUES (?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)');
$st->execute([$argv[1], password_hash($argv[2], PASSWORD_DEFAULT)]);
echo "Yönetici kaydedildi.\n";
