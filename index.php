<?php
require 'includes/init.php'; need();
$q = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$stats = [
  'Títulos'           => $q('SELECT COUNT(*) FROM libros'),
  'Ejemplares libres' => $q('SELECT COALESCE(SUM(disponibles),0) FROM libros'),
  'Valor inventario'  => $pdo->query('SELECT COALESCE(SUM(precio * ejemplares),0) FROM libros')->fetchColumn(),
  'Socios'            => $q('SELECT COUNT(*) FROM socios'),
  'Préstamos activos' => $q('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL'),
  'Vencidos'          => $q('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL AND fecha_limite < CURDATE()'),
];
$recientes = $pdo->query('SELECT p.*, l.titulo, s.nombre FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id ORDER BY p.id DESC LIMIT 6')->fetchAll();
head('Inicio'); ?>
<section class="dashboard-intro"><div><p>RESUMEN GENERAL</p><h2>Tu biblioteca, de un vistazo</h2><span>Indicadores actuales del catálogo y la circulación.</span></div><div class="dashboard-actions"><a class="btn alt" href="libros.php">Ver catálogo</a><a class="btn" href="prestamos.php">Nuevo préstamo</a></div></section>
<section class="dashboard-metrics" aria-label="Indicadores de la biblioteca">
  <?php $metricIcons = ['Títulos'=>'libros','Ejemplares libres'=>'inicio','Valor inventario'=>'valor','Socios'=>'socios','Préstamos activos'=>'prestamos','Vencidos'=>'prestamos']; foreach ($stats as $k => $v): ?>
    <article class="metric-card<?= $k === 'Vencidos' && $v ? ' metric-alert' : '' ?>">
      <div class="metric-top"><span><?= e($k) ?></span><span class="metric-icon"><?= nav_icon($metricIcons[$k]) ?></span></div>
      <strong><?= $k === 'Valor inventario' ? '$' . number_format((float)$v, 2) : number_format($v) ?></strong>
      <small><?= $k === 'Valor inventario' ? 'MXN · ejemplares totales' : ($k === 'Vencidos' ? ($v ? 'Requieren seguimiento' : 'Sin retrasos pendientes') : 'Total registrado') ?></small>
    </article>
  <?php endforeach; ?>
</section>
<section class="activity-section"><div class="section-heading"><div><p class="eyebrow">CIRCULACIÓN</p><h2>Últimos préstamos</h2></div><a href="prestamos.php">Ver todos <span aria-hidden="true">→</span></a></div>
<?php if (!$recientes): ?><p class="muted">Aún no hay préstamos. <a href="prestamos.php">Registra el primero</a>.</p>
<?php else: ?>
<div class="wrap"><table>
<thead><tr><th>LIBRO</th><th>SOCIO</th><th>FECHA LÍMITE</th><th>ESTADO</th></tr></thead><tbody>
<?php foreach ($recientes as $p): $est = $p['fecha_devolucion'] ? ['ok','Devuelto'] : ($p['fecha_limite'] < date('Y-m-d') ? ['bad','Vencido'] : ['act','Activo']); ?>
<tr><td><?= e($p['titulo']) ?></td><td><?= e($p['nombre']) ?></td><td><?= e($p['fecha_limite']) ?></td><td><span class="tag <?= $est[0] ?>"><?= $est[1] ?></span></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?></section><?php foot();
