<?php
function head($titulo) {
  need();
  $actual = basename($_SERVER["PHP_SELF"]);
  $nav = ["index.php"=>"Inicio","libros.php"=>"Libros","socios.php"=>"Socios","prestamos.php"=>"Préstamos"];
  if (is_admin()) $nav["usuarios.php"] = "Usuarios";
  $msg = flash(); ?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($titulo) ?> · Biblioteca Central</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<header class="top">
  <a class="brand" href="index.php">Biblioteca Central</a>
  <nav aria-label="Principal">
    <?php foreach ($nav as $f => $n): ?><a href="<?= $f ?>"<?= $f === $actual ? " class=\"on\" aria-current=\"page\"" : "" ?>><?= $n ?></a><?php endforeach; ?>
  </nav>
  <span class="user"><?= e($_SESSION["u"]["nombre"]) ?> · <a href="logout.php">Salir</a></span>
</header>
<main>
<h1><?= e($titulo) ?></h1>
<?php if ($msg): ?><p class="flash" role="status"><?= e($msg) ?></p><?php endif;
}
function foot() { ?>
</main>
<script src="assets/js/app.js"></script>
</body></html>
<?php }
