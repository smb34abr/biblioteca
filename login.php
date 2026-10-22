<?php
require 'includes/init.php';
// Primer arranque: crea el administrador si no existe ninguno
if (!$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn()) {
  $pdo->prepare('INSERT INTO usuarios (nombre,nombre_completo,email,password_hash,rol) VALUES (?,?,?,?,?)')
      ->execute(['Administrador', 'Administrador', 'admin@biblioteca.test', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check();
  $s = $pdo->prepare('SELECT * FROM usuarios WHERE email=?'); $s->execute([trim($_POST['email'])]);
  $u = $s->fetch();
  if ($u && password_verify($_POST['password'], $u['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['u'] = ['id' => $u['id'], 'nombre' => $u['nombre_completo'], 'rol' => $u['rol']];
    go('index.php');
  }
  $error = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acceso · Biblioteca Central</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css"></head>
<body class="auth">
<form method="post" class="login">
  <h1>Biblioteca Central</h1>
  <p class="muted">Inicia sesión para administrar el catálogo.</p>
  <?php if ($error): ?><p class="flash err" role="alert"><?= e($error) ?></p><?php endif; ?>
  <input type="hidden" name="t" value="<?= csrf() ?>">
  <label>Correo<input type="email" name="email" value="admin@biblioteca.test" required autofocus></label>
  <label>Contraseña<input type="password" name="password" required></label>
  <button class="btn">Entrar</button>
  <p class="muted small">Demo: admin@biblioteca.test / admin123</p>
</form></body></html>
