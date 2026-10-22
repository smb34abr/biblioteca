<?php
require 'includes/init.php'; need();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check();
  $pdo->beginTransaction();
  if (isset($_POST['devolver'])) {
    $s = $pdo->prepare('UPDATE prestamos SET fecha_devolucion=CURDATE(), multa=GREATEST(DATEDIFF(CURDATE(),fecha_limite),0)*? WHERE id=? AND fecha_devolucion IS NULL'); $s->execute([MULTA_POR_DIA, $_POST['devolver']]);
    if ($s->rowCount()) {
      $pdo->prepare('UPDATE libros SET disponibles=disponibles+1 WHERE id=(SELECT libro_id FROM prestamos WHERE id=?)')->execute([$_POST['devolver']]);
      flash('Devolución registrada.');
    }
  } else {
    $dias = min(60, max(1, (int)$_POST['dias']));
    $s = $pdo->prepare('UPDATE libros SET disponibles=disponibles-1 WHERE id=? AND disponibles>0'); $s->execute([$_POST['libro']]);
    if (!$s->rowCount()) flash('Ese libro no tiene ejemplares disponibles.');
    else {
      $pdo->prepare('INSERT INTO prestamos (libro_id,socio_id,fecha_prestamo,fecha_limite) VALUES (?,?,CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? DAY))')->execute([$_POST['libro'], $_POST['socio'], $dias]);
      flash('Préstamo registrado.');
    }
  }
  $pdo->commit(); go('prestamos.php');
}
$libros  = $pdo->query('SELECT id,titulo,disponibles FROM libros WHERE disponibles>0 ORDER BY titulo')->fetchAll();
$socios  = $pdo->query('SELECT id,nombre FROM socios ORDER BY nombre')->fetchAll();
$b = '%' . trim($_GET['q'] ?? '') . '%';
$s = $pdo->prepare('SELECT COUNT(*) FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id WHERE l.titulo LIKE ? OR s.nombre LIKE ?'); $s->execute([$b, $b]);
$pg = page_info($s->fetchColumn());
$s = $pdo->prepare('SELECT p.*, l.titulo, s.nombre FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id WHERE l.titulo LIKE ? OR s.nombre LIKE ? ORDER BY p.fecha_devolucion IS NOT NULL, p.fecha_limite LIMIT ? OFFSET ?');
$s->bindValue(1, $b); $s->bindValue(2, $b); $s->bindValue(3, 20, PDO::PARAM_INT); $s->bindValue(4, $pg['offset'], PDO::PARAM_INT); $s->execute(); $lista = $s->fetchAll();
head('Préstamos'); ?>
<p class="export"><a href="exportar.php?tipo=prestamos&amp;formato=csv&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>">Descargar CSV</a> <a href="exportar.php?tipo=prestamos&amp;formato=pdf&amp;q=<?= rawurlencode($_GET['q'] ?? '') ?>" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></p>
<form method="post" class="panel grid">
  <input type="hidden" name="t" value="<?= csrf() ?>">
  <label>Libro<select name="libro" required><option value="">Selecciona un libro</option><?php foreach ($libros as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['titulo']) ?> (<?= $l['disponibles'] ?>)</option><?php endforeach; ?></select></label>
  <label>Socio<select name="socio" required><option value="">Selecciona un socio</option><?php foreach ($socios as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['nombre']) ?></option><?php endforeach; ?></select></label>
  <label>Días de préstamo<input type="number" name="dias" min="1" max="60" value="14"></label>
  <div class="actions"><button class="btn">Registrar préstamo</button></div>
</form>
<form class="search"><input type="search" name="q" placeholder="Buscar libro o socio" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar préstamos"><button class="btn alt">Buscar</button></form>
<?php if (!$lista): ?><p class="muted">Todavía no hay préstamos registrados.</p><?php else: ?>
<div class="wrap"><table>
<tr><th>Libro</th><th>Socio</th><th>Prestado</th><th>Vence</th><th>Estado</th><th>Multa</th><th></th></tr>
<?php foreach ($lista as $p): $est = $p['fecha_devolucion'] ? ['ok','Devuelto'] : ($p['fecha_limite'] < date('Y-m-d') ? ['bad','Vencido'] : ['act','Activo']); ?>
<tr><td><?= e($p['titulo']) ?></td><td><?= e($p['nombre']) ?></td><td><?= e($p['fecha_prestamo']) ?></td><td><?= e($p['fecha_limite']) ?></td>
<td><span class="tag <?= $est[0] ?>"><?= $est[1] ?></span></td><td>$<?= number_format((float)$p['multa'], 2) ?></td>
<td><?php if (!$p['fecha_devolucion']): ?><form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><button class="link" name="devolver" value="<?= $p['id'] ?>">Registrar devolución</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table></div><?php page_links($pg['page'], $pg['pages']); ?><?php endif; foot();
