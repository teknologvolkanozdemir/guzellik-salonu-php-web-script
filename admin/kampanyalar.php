<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$table = 'campaigns';
$fields = ['title'=>['Başlık','req'],'description'=>['Açıklama','text'],'discount'=>['İndirim (ör. %15)','opt'],'starts_on'=>['Başlangıç','date'],'ends_on'=>['Bitiş','date']]; // alan => [etiket, tip]
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Geçersiz istek.'); }
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) {
        try { db()->prepare("DELETE FROM $table WHERE id=?")->execute([$id]); }
        catch (PDOException $e) { db()->prepare("UPDATE $table SET active=0 WHERE id=?")->execute([$id]); }
        header('Location: kampanyalar.php'); exit;
    }
    $vals = [];
    foreach ($fields as $f => [$label, $type]) {
        $val = trim((string)($_POST[$f] ?? ''));
        if ($type === 'req' && $val === '') $errors[$f] = "$label gerekli.";
        if ($type === 'num' && !is_numeric($val)) $errors[$f] = "$label sayı olmalı.";
        if ($type === 'date' && $val !== '' && !DateTime::createFromFormat('Y-m-d', $val)) $errors[$f] = "$label geçersiz.";
        $vals[$f] = ($type === 'date' && $val === '') ? null : $val;
    }
    $vals['active'] = isset($_POST['active']) ? 1 : 0;
    if (!$errors) {
        $cols = array_keys($vals);
        if ($id) db()->prepare("UPDATE $table SET " . implode(',', array_map(fn($c) => "$c=?", $cols)) . " WHERE id=?")->execute([...array_values($vals), $id]);
        else db()->prepare("INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")")->execute(array_values($vals));
        header('Location: kampanyalar.php'); exit;
    }
}
render_header('Kampanyalar', 'kampanyalar.php', true);
error_summary($errors);
$edit = null;
if (isset($_GET['id'])) { $s = db()->prepare("SELECT * FROM $table WHERE id=?"); $s->execute([(int)$_GET['id']]); $edit = $s->fetch() ?: null; }
$cur = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_merge($_POST) : ($edit ?? ['active' => 1]);
?>
<h2><?= $edit ? 'Düzenle' : 'Yeni ekle' ?></h2>
<form method="post" action="kampanyalar.php<?= $edit ? '?id=' . (int)$edit['id'] : '' ?>" novalidate>
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<?php foreach ($fields as $f => [$label, $type]): ?>
<label for="<?= e($f) ?>"><?= e($label) ?></label>
<?php if ($type === 'text'): ?><textarea id="<?= e($f) ?>" name="<?= e($f) ?>" rows="3"><?= e($cur[$f] ?? '') ?></textarea>
<?php else: ?><input id="<?= e($f) ?>" name="<?= e($f) ?>" type="<?= $type === 'date' ? 'date' : 'text' ?>"<?= $type === 'req' || $type === 'num' ? ' required' : '' ?> value="<?= e($cur[$f] ?? '') ?>"<?= field_attrs($f, $errors) ?>><?php endif; field_error($f, $errors); ?>
<?php endforeach; ?>
<label><input type="checkbox" name="active" value="1" style="width:auto"<?= !empty($cur['active']) ? ' checked' : '' ?>> Aktif</label>
<button type="submit">Kaydet</button>
</form>
<h2>Liste</h2>
<div class="table-wrap"><table><caption class="sr-only">Kampanyalar listesi</caption><thead><tr>
<?php foreach ($fields as $f => [$label, $type]) if ($type !== 'text') echo '<th scope="col">' . e($label) . '</th>'; ?>
<th scope="col">Durum</th><th scope="col">İşlem</th></tr></thead><tbody>
<?php foreach (db()->query("SELECT * FROM $table ORDER BY id DESC") as $r): ?>
<tr><?php foreach ($fields as $f => [$label, $type]) if ($type !== 'text') echo '<td>' . e($r[$f]) . '</td>'; ?>
<td><?= $r['active'] ? 'Aktif' : 'Pasif' ?></td>
<td><a href="kampanyalar.php?id=<?= (int)$r['id'] ?>">Düzenle<span class="sr-only">: <?= e($r[array_key_first($fields)]) ?></span></a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button type="submit" name="delete" value="1" class="danger" onclick="return confirm('Silinsin mi?')">Sil<span class="sr-only">: <?= e($r[array_key_first($fields)]) ?></span></button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php render_footer(true);
