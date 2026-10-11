<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();
$services = $pdo->query('SELECT id,name,duration_min,price FROM services WHERE active=1 ORDER BY category,name')->fetchAll();
$staff = $pdo->query('SELECT id,name FROM staff WHERE active=1 ORDER BY name')->fetchAll();
$errors = []; $ok = false;
$v = ['service_id'=>'','staff_id'=>'','customer_name'=>'','phone'=>'','email'=>'','appt_date'=>'','appt_time'=>'','note'=>''];
$open = max(0, (int)setting('open_hour')); $close = min(24, (int)setting('close_hour'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    $ids = array_column($services, 'id');
    if (!in_array((int)$v['service_id'], $ids, true)) $errors['service_id'] = 'Lütfen bir hizmet seçin.';
    if ($v['staff_id'] !== '' && !in_array((int)$v['staff_id'], array_column($staff, 'id'), true)) $errors['staff_id'] = 'Geçersiz personel seçimi.';
    if ($v['customer_name'] === '') $errors['customer_name'] = 'Lütfen adınızı girin.';
    if (!preg_match('/^[0-9 +()\-]{7,20}$/', $v['phone'])) $errors['phone'] = 'Geçerli bir telefon numarası girin.';
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    $d = DateTime::createFromFormat('Y-m-d', $v['appt_date']);
    if (!$d || $d->format('Y-m-d') !== $v['appt_date'] || $v['appt_date'] < date('Y-m-d')) $errors['appt_date'] = 'Bugün veya sonrası için geçerli bir tarih girin.';
    elseif ($d->format('w') === '0') $errors['appt_date'] = 'Pazar günleri kapalıyız.';
    $t = DateTime::createFromFormat('H:i', $v['appt_time']);
    if (!$t || $t->format('H:i') !== $v['appt_time'] || (int)$t->format('H') < $open || (int)$t->format('H') >= $close) $errors['appt_time'] = "Saat $open:00 ile $close:00 arasında olmalıdır.";
    if (!$errors) {
        $sid = $v['staff_id'] === '' ? null : (int)$v['staff_id'];
        if ($sid) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=? AND appt_date=? AND appt_time=? AND status<>'iptal'");
            $c->execute([$sid, $v['appt_date'], $v['appt_time'].':00']);
            if ($c->fetchColumn() > 0) $errors['appt_time'] = 'Bu personel seçilen saatte dolu. Lütfen başka saat seçin.';
        }
    }
    if (!$errors) {
        $pdo->prepare('INSERT INTO appointments (service_id,staff_id,customer_name,phone,email,appt_date,appt_time,note) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([(int)$v['service_id'], $sid, mb_substr($v['customer_name'],0,100), $v['phone'], $v['email'] ?: null, $v['appt_date'], $v['appt_time'].':00', $v['note']]);
        $ok = true; $v = array_map(fn($x) => '', $v);
    }
}
render_header('Randevu Al', 'randevu.php');
if ($ok) echo '<div class="alert alert-ok" role="status">Randevu talebiniz alındı. Onay için sizinle iletişime geçeceğiz.</div>';
error_summary($errors);
?>
<form method="post" action="randevu.php" novalidate>
<?= csrf_field() ?>
<p class="hint">* ile işaretli alanlar zorunludur.</p>
<label for="service_id">Hizmet *</label>
<select id="service_id" name="service_id" required<?= field_attrs('service_id',$errors) ?>>
<option value="">Seçiniz</option>
<?php foreach ($services as $s): ?><option value="<?= (int)$s['id'] ?>"<?= (string)$s['id']===$v['service_id']?' selected':'' ?>><?= e($s['name']) ?> (<?= (int)$s['duration_min'] ?> dk, <?= e(money($s['price'])) ?>)</option><?php endforeach; ?>
</select><?php field_error('service_id',$errors); ?>
<label for="staff_id">Personel</label>
<select id="staff_id" name="staff_id"<?= field_attrs('staff_id',$errors) ?>>
<option value="">Fark etmez</option>
<?php foreach ($staff as $p): ?><option value="<?= (int)$p['id'] ?>"<?= (string)$p['id']===$v['staff_id']?' selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
</select><?php field_error('staff_id',$errors); ?>
<label for="appt_date">Tarih *</label>
<input id="appt_date" name="appt_date" type="date" min="<?= e(date('Y-m-d')) ?>" required value="<?= e($v['appt_date']) ?>"<?= field_attrs('appt_date',$errors) ?>><?php field_error('appt_date',$errors); ?>
<label for="appt_time">Saat * <span class="hint">Çalışma saatleri <?= $open ?>:00–<?= $close ?>:00</span></label>
<input id="appt_time" name="appt_time" type="time" step="900" required value="<?= e($v['appt_time']) ?>"<?= field_attrs('appt_time',$errors) ?>><?php field_error('appt_time',$errors); ?>
<label for="customer_name">Adınız soyadınız *</label>
<input id="customer_name" name="customer_name" autocomplete="name" required value="<?= e($v['customer_name']) ?>"<?= field_attrs('customer_name',$errors) ?>><?php field_error('customer_name',$errors); ?>
<label for="phone">Telefon *</label>
<input id="phone" name="phone" type="tel" autocomplete="tel" required value="<?= e($v['phone']) ?>"<?= field_attrs('phone',$errors) ?>><?php field_error('phone',$errors); ?>
<label for="email">E-posta</label>
<input id="email" name="email" type="email" autocomplete="email" value="<?= e($v['email']) ?>"<?= field_attrs('email',$errors) ?>><?php field_error('email',$errors); ?>
<label for="note">Not</label>
<textarea id="note" name="note" rows="4"><?= e($v['note']) ?></textarea>
<button type="submit">Randevu talebi gönder</button>
</form>
<?php if ($errors): ?><script>document.getElementById('errors').focus();</script><?php endif;
render_footer();
