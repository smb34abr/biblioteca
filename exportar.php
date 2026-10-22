<?php
require 'includes/init.php'; need();

$tipo = $_GET['tipo'] ?? '';
$formato = $_GET['formato'] ?? 'csv';
$busqueda = trim($_GET['q'] ?? '');
$busquedaLike = '%' . $busqueda . '%';
$consultas = [
  'libros' => ['Libros', ['Título', 'Escritores', 'Editorial', 'ISBN', 'Categoría', 'Año', 'Precio por ejemplar (MXN)', 'Disponibles', 'Ejemplares'], 'SELECT l.titulo,COALESCE((SELECT GROUP_CONCAT(w.nombre ORDER BY w.nombre SEPARATOR ", ") FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id),l.autor),e.nombre,l.isbn,l.categoria,l.anio,l.precio,l.disponibles,l.ejemplares FROM libros l LEFT JOIN editoriales e ON e.id=l.editorial_id WHERE l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR e.nombre LIKE ? OR EXISTS (SELECT 1 FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id AND w.nombre LIKE ?) ORDER BY l.titulo', [$busquedaLike, $busquedaLike, $busquedaLike, $busquedaLike, $busquedaLike]],
  'socios' => ['Socios', ['Nombre', 'Correo', 'Teléfono', 'Creado'], 'SELECT nombre,email,telefono,creado FROM socios WHERE nombre LIKE ? OR email LIKE ? OR telefono LIKE ? ORDER BY nombre', [$busquedaLike, $busquedaLike, $busquedaLike]],
  'prestamos' => ['Préstamos', ['Libro', 'Socio', 'Préstamo', 'Límite', 'Devolución', 'Multa'], 'SELECT l.titulo,s.nombre,p.fecha_prestamo,p.fecha_limite,p.fecha_devolucion,p.multa FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id WHERE l.titulo LIKE ? OR s.nombre LIKE ? ORDER BY p.id DESC', [$busquedaLike, $busquedaLike]],
];
if (!isset($consultas[$tipo]) || !in_array($formato, ['csv', 'pdf'], true)) { http_response_code(400); exit('Solicitud de exportación no válida.'); }
[$titulo, $encabezados, $sql, $params] = $consultas[$tipo];
$stmt = $pdo->prepare($sql); $stmt->execute($params); $filas = $stmt->fetchAll(PDO::FETCH_NUM);
$nombre = $tipo . '-' . date('Y-m-d');

if ($formato === 'csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="' . $nombre . '.csv"');
  $salida = fopen('php://output', 'w');
  fwrite($salida, "\xEF\xBB\xBF");
  fputcsv($salida, $encabezados, ',', '"', '');
  foreach ($filas as $fila) {
    $fila = array_map(static function ($valor) {
      $valor = (string)($valor ?? '');
      return preg_match('/^[\s]*[=+@\-]/u', $valor) ? "'" . $valor : $valor;
    }, $fila);
    fputcsv($salida, $fila, ',', '"', '');
  }
  fclose($salida);
  exit;
}
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="' . $nombre . '.html"');
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($titulo) ?> · Biblioteca Central</title>
<style>body{font:14px Georgia,serif;color:#222;margin:32px}h1{font-size:24px}p{color:#555}button{padding:8px 14px;margin-bottom:16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #bbb;padding:7px;text-align:left}th{background:#eee}@media print{button{display:none}body{margin:12mm}tr{break-inside:avoid}}</style></head><body>
<button onclick="window.print()">Imprimir / Guardar como PDF</button><h1><?= e($titulo) ?> · Biblioteca Central</h1><p>Generado <?= e(date('Y-m-d H:i')) ?> · <?= count($filas) ?> registros</p>
<table><thead><tr><?php foreach ($encabezados as $encabezado): ?><th><?= e($encabezado) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($filas as $fila): ?><tr><?php foreach ($fila as $valor): ?><td><?= e($valor ?? '') ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table></body></html>