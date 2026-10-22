<?php
require 'includes/init.php'; require_admin();

$busqueda = trim($_GET['q'] ?? '');
$like = '%' . $busqueda . '%';
$filtroEscritor = 'EXISTS (SELECT 1 FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id AND w.nombre LIKE ?)';
$where = "(l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR e.nombre LIKE ? OR $filtroEscritor)";

$stmt = $pdo->prepare("SELECT COUNT(*) FROM libros l LEFT JOIN editoriales e ON e.id=l.editorial_id WHERE $where");
$stmt->execute([$like, $like, $like, $like, $like]);
$total = (int)$stmt->fetchColumn();
$paginacion = page_info($total);

$stmt = $pdo->prepare("SELECT l.id,l.titulo,l.isbn,l.categoria,l.anio,l.precio,l.ejemplares,l.disponibles,e.nombre AS editorial,COALESCE((SELECT GROUP_CONCAT(w.nombre ORDER BY w.nombre SEPARATOR ', ') FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id),l.autor) AS escritores FROM libros l LEFT JOIN editoriales e ON e.id=l.editorial_id WHERE $where ORDER BY l.titulo LIMIT ? OFFSET ?");
foreach ([$like, $like, $like, $like, $like] as $index => $value) $stmt->bindValue($index + 1, $value);
$stmt->bindValue(6, 20, PDO::PARAM_INT);
$stmt->bindValue(7, $paginacion['offset'], PDO::PARAM_INT);
$stmt->execute();
$libros = $stmt->fetchAll();

head('Reportes'); ?>
<section class="panel">
  <form method="get" class="search report-search">
    <label for="busqueda-reporte">Buscar en el catálogo</label>
    <div class="report-search-controls"><input id="busqueda-reporte" type="search" name="q" placeholder="Búsqueda por título, escritor, ISBN o editorial" value="<?= e($busqueda) ?>"><button class="btn">Buscar</button><?php if ($busqueda !== ''): ?><a class="btn alt" href="reportes.php">Limpiar</a><?php endif; ?></div>
  </form>
  <div class="report-toolbar"><span><?= number_format($total) ?> libros encontrados</span><div class="report-actions"><a class="btn alt" href="exportar.php?tipo=libros&amp;formato=csv&amp;q=<?= rawurlencode($busqueda) ?>">Descargar CSV</a><a class="btn" href="exportar.php?tipo=libros&amp;formato=pdf&amp;q=<?= rawurlencode($busqueda) ?>" target="_blank" rel="noopener">Imprimir / Guardar como PDF</a></div></div>
</section>
<?php if (!$libros): ?><p class="muted">No hay libros que coincidan con esa búsqueda.</p><?php else: ?>
<div class="wrap"><table><thead><tr><th>TÍTULO</th><th>ESCRITOR</th><th>ISBN</th><th>EDITORIAL</th><th>CATEGORÍA</th><th>PRECIO</th><th>EJEMPLARES</th></tr></thead><tbody>
<?php foreach ($libros as $libro): ?><tr><td><?= e($libro['titulo']) ?></td><td><?= e($libro['escritores']) ?></td><td><?= e($libro['isbn']) ?></td><td><?= e($libro['editorial'] ?: 'Sin editorial') ?></td><td><?= e($libro['categoria']) ?></td><td>$<?= number_format((float)$libro['precio'], 2) ?> MXN</td><td><?= (int)$libro['disponibles'] ?> / <?= (int)$libro['ejemplares'] ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php page_links($paginacion['page'], $paginacion['pages']); ?>
<?php endif; foot();