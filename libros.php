<?php
require 'includes/init.php'; need();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!is_admin()) { http_response_code(403); exit('Solo admin puede modificar libros.'); }
  check();
  if (isset($_POST['del'])) {
    $n = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE libro_id=? AND fecha_devolucion IS NULL'); $n->execute([$_POST['del']]);
    if ($n->fetchColumn()) flash('No se puede eliminar: el libro tiene préstamos activos.');
    else try { $pdo->prepare('DELETE FROM libros WHERE id=?')->execute([$_POST['del']]); flash('Libro eliminado.'); }
         catch (PDOException $x) { flash('No se puede eliminar: tiene historial de préstamos.'); }
  } else {
    $precio = trim($_POST['precio'] ?? '');
    $d = [trim($_POST['titulo']), trim($_POST['autor']), trim($_POST['isbn']) ?: null, trim($_POST['categoria']), (int)$_POST['anio'] ?: null, (float)$precio, max(1, (int)$_POST['ejemplares'])];
    if (!$d[0] || !$d[1]) flash('Título y autor son obligatorios.');
    elseif ($precio === '' || !is_numeric($precio) || (float)$precio < 0 || (float)$precio > 99999999.99) flash('Escribe un precio válido, mayor o igual a cero.');
    else try {
      if ($_POST['id']) {
        // disponibles se ajusta según el cambio en ejemplares (se evalúa antes de actualizar ejemplares)
        $pdo->prepare('UPDATE libros SET titulo=?,autor=?,isbn=?,categoria=?,anio=?,precio=?,disponibles=GREATEST(0,disponibles+(?-ejemplares)),ejemplares=? WHERE id=?')
            ->execute([...array_slice($d, 0, 6), $d[6], $d[6], $_POST['id']]);
        flash('Libro actualizado.');
      } else {
        $pdo->prepare('INSERT INTO libros (titulo,autor,isbn,categoria,anio,precio,ejemplares,disponibles) VALUES (?,?,?,?,?,?,?,?)')->execute([...$d, $d[6]]);
        flash('Libro agregado.');
      }
    } catch (PDOException $x) { flash('Ese ISBN ya está registrado.'); }
  }
  go('libros.php');
}
if (isset($_GET['edit']) && !is_admin()) { http_response_code(403); exit('Solo admin puede modificar libros.'); }
$edit = [];
if (isset($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM libros WHERE id=?'); $s->execute([$_GET['edit']]); $edit = $s->fetch() ?: []; }
$b = '%' . trim($_GET['q'] ?? '') . '%';
$s = $pdo->prepare('SELECT COUNT(*) FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?'); $s->execute([$b, $b, $b]);
$pg = page_info($s->fetchColumn());
$s = $pdo->prepare('SELECT * FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ? ORDER BY titulo LIMIT ? OFFSET ?');
$s->bindValue(1, $b); $s->bindValue(2, $b); $s->bindValue(3, $b); $s->bindValue(4, 20, PDO::PARAM_INT); $s->bindValue(5, $pg['offset'], PDO::PARAM_INT); $s->execute();
$libros = $s->fetchAll();
head('Libros'); ?>
<p class="export"><a href="exportar.php?tipo=libros&amp;formato=csv&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>">Descargar CSV</a> <a href="exportar.php?tipo=libros&amp;formato=pdf&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></p>
<?php if (is_admin()): ?>
<form method="post" class="panel grid">
  <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
  <label>Título<input name="titulo" value="<?= e($edit['titulo'] ?? '') ?>" required></label>
  <label>Autor<input name="autor" value="<?= e($edit['autor'] ?? '') ?>" required></label>
  <label>ISBN<input name="isbn" value="<?= e($edit['isbn'] ?? '') ?>"></label>
  <label>Categoría<input name="categoria" value="<?= e($edit['categoria'] ?? '') ?>"></label>
  <label>Año<input type="number" name="anio" min="1000" max="2100" value="<?= e($edit['anio'] ?? '') ?>"></label>
  <label>Precio por ejemplar (MXN)<input type="number" name="precio" min="0" max="99999999.99" step="0.01" value="<?= e($edit['precio'] ?? '0.00') ?>" required></label>
  <label>Ejemplares<input type="number" name="ejemplares" min="1" value="<?= e($edit['ejemplares'] ?? 1) ?>"></label>
  <div class="actions"><button class="btn"><?= $edit ? 'Guardar cambios' : 'Agregar libro' ?></button><?php if ($edit): ?><a href="libros.php">Cancelar</a><?php endif; ?></div>
</form>
<?php endif; ?>
<form class="search"><input type="search" name="q" placeholder="Buscar por título, autor o ISBN" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar libros"><button class="btn alt">Buscar</button></form>
<?php if (!$libros): ?><p class="muted">No hay libros que coincidan con la búsqueda.</p><?php else: ?>
<div class="wrap"><table>
<tr><th>Título</th><th>Autor</th><th>Categoría</th><th>Año</th><th>Precio</th><th>Disponibles</th><th></th></tr>
<?php foreach ($libros as $l): ?>
<tr><td><?= e($l['titulo']) ?><small><?= e($l['isbn']) ?></small></td><td><?= e($l['autor']) ?></td><td><?= e($l['categoria']) ?></td><td><?= e($l['anio']) ?></td>
<td>$<?= number_format((float)$l['precio'], 2) ?> MXN</td>
<td><span class="tag <?= $l['disponibles'] ? 'ok' : 'bad' ?>"><?= $l['disponibles'] ?> / <?= $l['ejemplares'] ?></span></td>
<td class="row"><?php if (is_admin()): ?><a href="?edit=<?= $l['id'] ?>">Editar</a>
<form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><button class="link danger" name="del" value="<?= $l['id'] ?>" data-confirm="¿Eliminar «<?= e($l['titulo']) ?>»?">Eliminar</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table></div><?php endif; ?>
<?php page_links($pg['page'], $pg['pages']); foot();
