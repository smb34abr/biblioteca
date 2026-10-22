<?php
function nav_icon($name) {
  $icons = [
    'inicio' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-6H10v6H4a1 1 0 0 1-1-1z"/>',
    'libros' => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 6h8M8 9h8"/>',
    'socios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'prestamos' => '<path d="M4 5h16M4 12h16M4 19h16"/><circle cx="7" cy="5" r="1"/><circle cx="7" cy="12" r="1"/><circle cx="7" cy="19" r="1"/>',
    'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>',
  ];
  return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? '') . '</svg>';
}

function head($titulo) {
  need();
  $actual = basename($_SERVER["PHP_SELF"]);
  $nav = ["index.php"=>["Inicio","inicio"],"libros.php"=>["Libros","libros"],"socios.php"=>["Socios","socios"],"prestamos.php"=>["Préstamos","prestamos"]];
  if (is_admin()) $nav["usuarios.php"] = ["Usuarios","usuarios"];
  $msg = flash(); ?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($titulo) ?> · Biblioteca Central</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head><body class="app-shell">
<aside class="sidebar" id="app-sidebar">
  <a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true"><?= nav_icon('libros') ?></span><span>Biblioteca<span class="brand-sub">CENTRAL</span></span></a>
  <p class="sidebar-label">GESTIÓN</p>
  <nav class="side-nav" aria-label="Principal">
    <?php foreach ($nav as $file => [$label, $icon]): ?>
      <a href="<?= e($file) ?>" class="nav-link<?= $file === $actual ? ' active' : '' ?>"<?= $file === $actual ? ' aria-current="page"' : '' ?>><?= nav_icon($icon) ?><span><?= e($label) ?></span></a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-bottom"><span class="status-dot"></span><span>Sistema operativo</span></div>
</aside>
<button class="sidebar-scrim" type="button" data-menu-close aria-label="Cerrar navegación"></button>
<div class="workspace">
  <header class="topbar">
    <button class="icon-button menu-toggle" type="button" data-menu-toggle aria-label="Abrir navegación" aria-controls="app-sidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    <div class="breadcrumbs"><span>Biblioteca Central</span><span aria-hidden="true">/</span><strong><?= e($titulo) ?></strong></div>
    <div class="account"><span class="account-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($_SESSION['u']['nombre'], 0, 1))) ?></span><span class="account-name"><?= e($_SESSION['u']['nombre']) ?><small><?= is_admin() ? 'Administrador' : 'Bibliotecario' ?></small></span><a class="logout-link" href="logout.php">Salir</a></div>
  </header>
  <main class="main-content">
    <div class="page-heading"><div><p class="eyebrow">BIBLIOTECA CENTRAL</p><h1><?= e($titulo) ?></h1></div></div>
    <?php if ($msg): ?><p class="flash" role="status"><?= e($msg) ?></p><?php endif;
}
function foot() { ?>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body></html>
<?php }
