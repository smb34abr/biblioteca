<?php
require 'includes/init.php'; require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check();
  $id = (int)($_POST['id'] ?? 0);
  if (isset($_POST['eliminar'])) {
    try {
      $pdo->prepare('DELETE FROM editoriales WHERE id=?')->execute([$id]);
      flash('Editorial eliminada. Los libros asociados quedaron sin editorial.');
    } catch (PDOException $x) { flash('No se pudo eliminar la editorial.'); }
  } else {
    $nombre = trim($_POST['nombre'] ?? '');
    $pais = trim($_POST['pais'] ?? '') ?: null;
    if (!$nombre) flash('El nombre de la editorial es obligatorio.');
    else try {
      if ($id) {
        $pdo->prepare('UPDATE editoriales SET nombre=?,pais=? WHERE id=?')->execute([$nombre, $pais, $id]);
        flash('Editorial actualizada.');
      } else {
        $pdo->prepare('INSERT INTO editoriales (nombre,pais) VALUES (?,?)')->execute([$nombre, $pais]);
        flash('Editorial agregada.');
      }
    } catch (PDOException $x) { flash('Ya existe una editorial con ese nombre.'); }
  }
  go('editoriales.php');
}

$edit = [];
if (isset($_GET['edit'])) {
  $stmt = $pdo->prepare('SELECT * FROM editoriales WHERE id=?');
  $stmt->execute([(int)$_GET['edit']]);
  $edit = $stmt->fetch() ?: [];
}
$total = (int)$pdo->query('SELECT COUNT(*) FROM editoriales')->fetchColumn();
$pg = page_info($total);
$stmt = $pdo->prepare('SELECT e.*,COUNT(l.id) AS libros FROM editoriales e LEFT JOIN libros l ON l.editorial_id=e.id GROUP BY e.id ORDER BY e.nombre LIMIT ? OFFSET ?');
$stmt->bindValue(1, 20, PDO::PARAM_INT);
$stmt->bindValue(2, $pg['offset'], PDO::PARAM_INT);
$stmt->execute();
$editoriales = $stmt->fetchAll();
head('Editoriales'); ?>
<section class="panel editorial-panel">
  <div class="section-heading"><div><p class="eyebrow">CATÁLOGO</p><h2><?= $edit ? 'Editar editorial' : 'Agregar editorial' ?></h2></div></div>
  <form method="post" class="grid">
    <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
    <label>Nombre<input name="nombre" maxlength="120" value="<?= e($edit['nombre'] ?? '') ?>" required></label>
    <label>País<input name="pais" maxlength="80" value="<?= e($edit['pais'] ?? '') ?>"></label>
    <div class="actions"><button class="btn"><?= $edit ? 'Guardar cambios' : 'Agregar editorial' ?></button><?php if ($edit): ?><a href="editoriales.php">Cancelar</a><?php endif; ?></div>
  </form>
</section>
<div class="report-toolbar editorial-report-toolbar"><span><?= number_format($total) ?> editoriales encontradas</span><div class="report-actions"><a class="btn alt" href="exportar.php?tipo=editoriales&amp;formato=csv">Descargar CSV</a><a class="btn" href="exportar.php?tipo=editoriales&amp;formato=pdf" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></div></div>
<?php if (!$editoriales): ?><p class="muted">Aún no hay editoriales registradas.</p><?php else: ?>
<div class="wrap"><table><thead><tr><th>EDITORIAL</th><th>PAÍS</th><th>LIBROS ASOCIADOS</th><th></th></tr></thead><tbody>
<?php foreach ($editoriales as $editorial): ?><tr><td><?= e($editorial['nombre']) ?></td><td><?= e($editorial['pais'] ?: '—') ?></td><td><?= (int)$editorial['libros'] ?></td><td class="row"><a href="?edit=<?= (int)$editorial['id'] ?>">Editar</a><form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= (int)$editorial['id'] ?>"><button class="link danger" name="eliminar" value="1" data-confirm="¿Eliminar la editorial <?= e($editorial['nombre']) ?>? Los libros asociados conservarán sus datos.">Eliminar</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php page_links($pg); ?>
<?php foot();