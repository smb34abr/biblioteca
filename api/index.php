<?php
require __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
  $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $ex) {
  http_response_code(500);
  echo json_encode(['error' => 'No se pudo conectar a la base de datos.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$username = $_SERVER['PHP_AUTH_USER'] ?? '';
$password = $_SERVER['PHP_AUTH_PW'] ?? '';
$auth = $pdo->prepare('SELECT id,nombre,email,rol,password_hash FROM usuarios WHERE email=?');
$auth->execute([$username]);
$user = $auth->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
  header('WWW-Authenticate: Basic realm="Biblioteca API"');
  http_response_code(401);
  echo json_encode(['error' => 'Autenticación requerida.'], JSON_UNESCAPED_UNICODE);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  header('Allow: GET');
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$recurso = $_GET['recurso'] ?? '';
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = min(100, max(1, (int)($_GET['limite'] ?? 20)));
$offset = ($pagina - 1) * $porPagina;
$busqueda = '%' . trim($_GET['q'] ?? '') . '%';
$recursos = [
  'libros' => ['SELECT COUNT(*) FROM libros l LEFT JOIN editoriales e ON e.id=l.editorial_id WHERE l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR e.nombre LIKE ? OR EXISTS (SELECT 1 FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id AND w.nombre LIKE ?)', 'SELECT l.id,l.titulo,l.autor,COALESCE((SELECT GROUP_CONCAT(w.nombre ORDER BY w.nombre SEPARATOR ", ") FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id),l.autor) AS escritores,l.isbn,l.categoria,l.anio,l.precio,l.editorial_id,e.nombre AS editorial,l.ejemplares,l.disponibles FROM libros l LEFT JOIN editoriales e ON e.id=l.editorial_id WHERE l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR e.nombre LIKE ? OR EXISTS (SELECT 1 FROM libro_escritor le JOIN escritores w ON w.id=le.escritor_id WHERE le.libro_id=l.id AND w.nombre LIKE ?) ORDER BY l.titulo LIMIT ? OFFSET ?', [$busqueda, $busqueda, $busqueda, $busqueda, $busqueda]],
  'socios' => ['SELECT COUNT(*) FROM socios WHERE nombre LIKE ? OR email LIKE ? OR telefono LIKE ?', 'SELECT id,nombre,email,telefono,creado FROM socios WHERE nombre LIKE ? OR email LIKE ? OR telefono LIKE ? ORDER BY nombre LIMIT ? OFFSET ?', [$busqueda, $busqueda, $busqueda]],
  'prestamos' => ['SELECT COUNT(*) FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id WHERE l.titulo LIKE ? OR s.nombre LIKE ?', 'SELECT p.id,p.libro_id,p.socio_id,l.titulo AS libro,s.nombre AS socio,p.fecha_prestamo,p.fecha_limite,p.fecha_devolucion,p.multa FROM prestamos p JOIN libros l ON l.id=p.libro_id JOIN socios s ON s.id=p.socio_id WHERE l.titulo LIKE ? OR s.nombre LIKE ? ORDER BY p.id DESC LIMIT ? OFFSET ?', [$busqueda, $busqueda]],
];
if (!isset($recursos[$recurso])) {
  http_response_code(400);
  echo json_encode(['error' => 'Recurso no válido.', 'recursos' => array_keys($recursos)], JSON_UNESCAPED_UNICODE);
  exit;
}
[$sqlCount, $sqlData, $params] = $recursos[$recurso];
$count = $pdo->prepare($sqlCount); $count->execute($params); $total = (int)$count->fetchColumn();
$data = $pdo->prepare($sqlData);
foreach ($params as $index => $value) $data->bindValue($index + 1, $value);
$data->bindValue(count($params) + 1, $porPagina, PDO::PARAM_INT);
$data->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$data->execute();
echo json_encode(['datos' => $data->fetchAll(), 'paginacion' => ['pagina' => $pagina, 'limite' => $porPagina, 'total' => $total, 'paginas' => (int)ceil($total / $porPagina)]], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);