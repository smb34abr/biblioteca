<?php
require 'includes/init.php'; require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check();
  $id = (int)($_POST['id'] ?? 0);
  if (isset($_POST['eliminar'])) {
    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('SELECT libro_id FROM libro_escritor WHERE escritor_id=?');
      $stmt->execute([$id]);
      $libroIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
      $pdo->prepare('DELETE FROM escritores WHERE id=?')->execute([$id]);
      $refresh = $pdo->prepare('UPDATE libros SET autor=COALESCE((SELECT GROUP_CONCAT(e.nombre ORDER BY e.nombre SEPARATOR ", ") FROM libro_escritor le JOIN escritores e ON e.id=le.escritor_id WHERE le.libro_id=libros.id), "") WHERE id=?');
      foreach ($libroIds as $libroId) $refresh->execute([$libroId]);
      $pdo->commit();
      flash('Escritor eliminado. Los libros se conservaron y se actualizaron sus relaciones.');
    } catch (PDOException $x) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      flash('No se pudo eliminar el escritor.');
    }
  } else {
    $nombre = trim($_POST['nombre'] ?? '');
    $pais = trim($_POST['pais'] ?? '') ?: null;
    if (!$nombre) flash('El nombre del escritor es obligatorio.');
    else try {
      if ($id) {
        $pdo->prepare('UPDATE escritores SET nombre=?,pais=? WHERE id=?')->execute([$nombre, $pais, $id]);
        $pdo->prepare('UPDATE libros SET autor=(SELECT GROUP_CONCAT(e.nombre ORDER BY e.nombre SEPARATOR ", ") FROM libro_escritor le JOIN escritores e ON e.id=le.escritor_id WHERE le.libro_id=libros.id) WHERE EXISTS (SELECT 1 FROM libro_escritor WHERE libro_id=libros.id)')->execute();
        flash('Escritor actualizado.');
      } else {
        $pdo->prepare('INSERT INTO escritores (nombre,pais) VALUES (?,?)')->execute([$nombre, $pais]);
        flash('Escritor agregado.');
      }
    } catch (PDOException $x) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      flash('Ya existe un escritor con ese nombre o no se pudieron actualizar sus libros.');
    }
  }
  go('escritores.php');
}

$edit = [];
if (isset($_GET['edit'])) {
  $stmt = $pdo->prepare('SELECT * FROM escritores WHERE id=?');
  $stmt->execute([(int)$_GET['edit']]);
  $edit = $stmt->fetch() ?: [];
}
$escritores = $pdo->query('SELECT e.*,COUNT(le.libro_id) AS libros FROM escritores e LEFT JOIN libro_escritor le ON le.escritor_id=e.id GROUP BY e.id ORDER BY e.nombre')->fetchAll();
head('Escritores'); ?>
<section class="panel editorial-panel">
  <div class="section-heading"><div><p class="eyebrow">CATÁLOGO</p><h2><?= $edit ? 'Editar escritor' : 'Agregar escritor' ?></h2></div></div>
  <form method="post" class="grid">
    <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
    <label>Nombre<input name="nombre" maxlength="120" value="<?= e($edit['nombre'] ?? '') ?>" required></label>
    <label>País<input name="pais" maxlength="80" value="<?= e($edit['pais'] ?? '') ?>"></label>
    <div class="actions"><button class="btn"><?= $edit ? 'Guardar cambios' : 'Agregar escritor' ?></button><?php if ($edit): ?><a href="escritores.php">Cancelar</a><?php endif; ?></div>
  </form>
</section>
<div class="report-toolbar editorial-report-toolbar"><span><?= number_format(count($escritores)) ?> escritores encontrados</span><div class="report-actions"><a class="btn alt" href="exportar.php?tipo=escritores&amp;formato=csv">Descargar CSV</a><a class="btn" href="exportar.php?tipo=escritores&amp;formato=pdf" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></div></div>
<?php if (!$escritores): ?><p class="muted">Aún no hay escritores registrados.</p><?php else: ?>
<div class="wrap"><table><thead><tr><th>ESCRITOR</th><th>PAÍS</th><th>LIBROS ASOCIADOS</th><th></th></tr></thead><tbody>
<?php foreach ($escritores as $escritor): ?><tr><td><?= e($escritor['nombre']) ?></td><td><?= e($escritor['pais'] ?: '—') ?></td><td><?= (int)$escritor['libros'] ?></td><td class="row"><a href="?edit=<?= (int)$escritor['id'] ?>">Editar</a><form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= (int)$escritor['id'] ?>"><button class="link danger" name="eliminar" value="1" data-confirm="¿Eliminar a <?= e($escritor['nombre']) ?>? Se quitará la relación con sus libros.">Eliminar</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php foot();