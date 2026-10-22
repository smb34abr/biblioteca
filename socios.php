<?php
require 'includes/init.php'; need();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!is_admin()) { http_response_code(403); exit('Solo admin puede modificar socios.'); }
  check();
  if (isset($_POST['del'])) {
    try { $pdo->prepare('DELETE FROM socios WHERE id=?')->execute([$_POST['del']]); flash('Socio eliminado.'); }
    catch (PDOException $x) { flash('No se puede eliminar: el socio tiene préstamos registrados.'); }
  } else {
    $d = [trim($_POST['nombre']), trim($_POST['email']), trim($_POST['telefono'])];
    if (!$d[0] || !filter_var($d[1], FILTER_VALIDATE_EMAIL)) flash('Escribe un nombre y un correo válido.');
    else try {
      if ($_POST['id']) { $pdo->prepare('UPDATE socios SET nombre=?,email=?,telefono=? WHERE id=?')->execute([...$d, $_POST['id']]); flash('Socio actualizado.'); }
      else { $pdo->prepare('INSERT INTO socios (nombre,email,telefono) VALUES (?,?,?)')->execute($d); flash('Socio agregado.'); }
    } catch (PDOException $x) { flash('Ese correo ya está registrado.'); }
  }
  go('socios.php');
}
if (isset($_GET['edit']) && !is_admin()) { http_response_code(403); exit('Solo admin puede modificar socios.'); }
$edit = [];
if (isset($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM socios WHERE id=?'); $s->execute([$_GET['edit']]); $edit = $s->fetch() ?: []; }
$b = '%' . trim($_GET['q'] ?? '') . '%';
$s = $pdo->prepare('SELECT COUNT(*) FROM socios WHERE nombre LIKE ? OR email LIKE ? OR telefono LIKE ?'); $s->execute([$b, $b, $b]);
$pg = page_info($s->fetchColumn());
$s = $pdo->prepare('SELECT s.*, (SELECT COUNT(*) FROM prestamos p WHERE p.socio_id=s.id AND p.fecha_devolucion IS NULL) activos FROM socios s WHERE s.nombre LIKE ? OR s.email LIKE ? OR s.telefono LIKE ? ORDER BY nombre LIMIT ? OFFSET ?');
$s->bindValue(1, $b); $s->bindValue(2, $b); $s->bindValue(3, $b); $s->bindValue(4, 20, PDO::PARAM_INT); $s->bindValue(5, $pg['offset'], PDO::PARAM_INT); $s->execute(); $socios = $s->fetchAll();
head('Socios'); ?>
<p class="export"><a href="exportar.php?tipo=socios&amp;formato=csv&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>">Descargar CSV</a> <a href="exportar.php?tipo=socios&amp;formato=pdf&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></p>
<?php if (is_admin()): ?>
<form method="post" class="panel grid">
  <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
  <label>Nombre<input name="nombre" value="<?= e($edit['nombre'] ?? '') ?>" required></label>
  <label>Correo<input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></label>
  <label>Teléfono<input name="telefono" value="<?= e($edit['telefono'] ?? '') ?>"></label>
  <div class="actions"><button class="btn"><?= $edit ? 'Guardar cambios' : 'Agregar socio' ?></button><?php if ($edit): ?><a href="socios.php">Cancelar</a><?php endif; ?></div>
</form>
<?php endif; ?>
<form class="search"><input type="search" name="q" placeholder="Buscar por nombre, correo o teléfono" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar socios"><button class="btn alt">Buscar</button></form>
<?php if (!$socios): ?><p class="muted">Aún no hay socios registrados.</p><?php else: ?>
<div class="wrap"><table id="tabla">
<tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Préstamos activos</th><th></th></tr>
<?php foreach ($socios as $s): ?>
<tr><td><?= e($s['nombre']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['telefono']) ?></td><td><?= $s['activos'] ?></td>
<td class="row"><?php if (is_admin()): ?><a href="?edit=<?= $s['id'] ?>">Editar</a>
<form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><button class="link danger" name="del" value="<?= $s['id'] ?>" data-confirm="¿Eliminar a <?= e($s['nombre']) ?>?">Eliminar</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table></div><?php endif; ?>
<?php page_links($pg); foot();
