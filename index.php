<?php
require 'includes/init.php'; need();
$q = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$stats = [
  'Títulos'           => $q('SELECT COUNT(*) FROM libros'),
  'Ejemplares libres' => $q('SELECT COALESCE(SUM(disponibles),0) FROM libros'),
  'Socios'            => $q('SELECT COUNT(*) FROM socios'),
  'Préstamos activos' => $q('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL'),
  'Vencidos'          => $q('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL AND fecha_limite < CURDATE()'),
];
$recientes = $pdo->query('SELECT p.*, l.titulo, s.nombre FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id ORDER BY p.id DESC LIMIT 6')->fetchAll();
head('Inicio'); ?>
<section class="ledger">
  <?php foreach ($stats as $k => $v): ?><div class="<?= $k === 'Vencidos' && $v ? 'warn' : '' ?>"><b><?= $v ?></b><span><?= e($k) ?></span></div><?php endforeach; ?>
</section>
<h2>Últimos préstamos</h2>
<?php if (!$recientes): ?><p class="muted">Aún no hay préstamos. <a href="prestamos.php">Registra el primero</a>.</p>
<?php else: ?>
<div class="wrap"><table>
<tr><th>Libro</th><th>Socio</th><th>Vence</th><th>Estado</th></tr>
<?php foreach ($recientes as $p): $est = $p['fecha_devolucion'] ? ['ok','Devuelto'] : ($p['fecha_limite'] < date('Y-m-d') ? ['bad','Vencido'] : ['act','Activo']); ?>
<tr><td><?= e($p['titulo']) ?></td><td><?= e($p['nombre']) ?></td><td><?= e($p['fecha_limite']) ?></td><td><span class="tag <?= $est[0] ?>"><?= $est[1] ?></span></td></tr>
<?php endforeach; ?></table></div>
<?php endif; foot();
