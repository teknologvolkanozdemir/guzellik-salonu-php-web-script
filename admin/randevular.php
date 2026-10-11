<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$st = ['beklemede' => 'Beklemede', 'onaylandi' => 'Onaylandı', 'iptal' => 'İptal'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) db()->prepare('DELETE FROM appointments WHERE id=?')->execute([$id]);
    elseif (isset($st[$_POST['status'] ?? ''])) db()->prepare('UPDATE appointments SET status=? WHERE id=?')->execute([$_POST['status'], $id]);
    header('Location: randevular.php'); exit;
}
render_header('Randevular', 'randevular.php', true);
$rows = db()->query('SELECT a.*, s.name AS service, p.name AS staff FROM appointments a JOIN services s ON s.id=a.service_id LEFT JOIN staff p ON p.id=a.staff_id ORDER BY appt_date DESC, appt_time DESC LIMIT 200')->fetchAll();
?>
<div class="table-wrap"><table><caption class="sr-only">Randevu listesi</caption>
<thead><tr><th scope="col">Tarih</th><th scope="col">Müşteri</th><th scope="col">Hizmet</th><th scope="col">Personel</th><th scope="col">Durum</th><th scope="col">İşlem</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['appt_date']) ?> <?= e(substr($r['appt_time'],0,5)) ?></td>
<td><?= e($r['customer_name']) ?><br><a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a><?php if ($r['note']) echo '<br>Not: ' . e($r['note']); ?></td>
<td><?= e($r['service']) ?></td><td><?= e($r['staff'] ?? 'Fark etmez') ?></td><td><?= e($st[$r['status']]) ?></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label for="st<?= (int)$r['id'] ?>" class="sr-only"><?= e($r['customer_name']) ?> randevu durumu</label>
<select id="st<?= (int)$r['id'] ?>" name="status"><?php foreach ($st as $k => $l): ?><option value="<?= e($k) ?>"<?= $k === $r['status'] ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
<button type="submit">Güncelle</button>
<button type="submit" name="delete" value="1" class="danger" onclick="return confirm('Bu randevu silinsin mi?')">Sil<span class="sr-only"> (<?= e($r['customer_name']) ?>)</span></button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php render_footer(true);
