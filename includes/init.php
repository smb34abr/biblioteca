<?php
require __DIR__ . "/../config/db.php";
session_start();
try {
  $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $ex) { http_response_code(500); exit("No se pudo conectar a la base de datos. Revisa config/db.php"); }

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }
function csrf() { return $_SESSION["t"] ??= bin2hex(random_bytes(16)); }
function check() { if (!hash_equals($_SESSION["t"] ?? "", $_POST["t"] ?? "")) { http_response_code(419); exit("Token inválido"); } }
function need() {
  static $checked = false;
  if ($checked) return;
  global $pdo;
  if (empty($_SESSION["u"])) { header("Location: login.php"); exit; }
  $s = $pdo->prepare('SELECT nombre,rol FROM usuarios WHERE id=?');
  $s->execute([$_SESSION['u']['id']]);
  $user = $s->fetch();
  if (!$user) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
  $_SESSION['u']['nombre'] = $user['nombre'];
  $_SESSION['u']['rol'] = $user['rol'];
  $checked = true;
}
function is_admin() { return ($_SESSION["u"]["rol"] ?? "") === "admin"; }
function require_admin() { need(); if (!is_admin()) { http_response_code(403); exit("Acceso denegado."); } }
function flash($m = null) { if ($m !== null) { $_SESSION["f"] = $m; return; } $x = $_SESSION["f"] ?? ""; unset($_SESSION["f"]); return $x; }
function go($url) { header("Location: $url"); exit; }
function page_info($total, $perPage = 20) {
  $pages = max(1, (int)ceil($total / $perPage));
  $page = min($pages, max(1, (int)($_GET["page"] ?? 1)));
  return ["page" => $page, "pages" => $pages, "offset" => ($page - 1) * $perPage];
}
function page_links($page, $pages) {
  if ($pages < 2) return;
  echo '<nav class="pagination" aria-label="Paginación">';
  foreach ([[max(1, $page - 1), "Anterior"], [$page, "$page / $pages"], [min($pages, $page + 1), "Siguiente"]] as [$number, $label]) {
    $query = $_GET;
    $query["page"] = $number;
    echo '<a href="?' . e(http_build_query($query)) . '"' . ($number === $page ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
  }
  echo '</nav>';
}

require __DIR__ . "/layout.php";
