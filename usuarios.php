<?php
require 'includes/init.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check();
  $id = (int)($_POST['id'] ?? 0);
  if (isset($_POST['crear'])) {
    $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    if (!$nombreCompleto || mb_strlen($nombreCompleto) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || !in_array($rol, ['admin', 'bibliotecario'], true)) {
      flash('Revisa los datos: escribe el nombre completo (máximo 150 caracteres), un correo válido y una contraseña de al menos 10 caracteres.');
    } else {
      try {
        $pdo->prepare('INSERT INTO usuarios (nombre,nombre_completo,email,password_hash,rol) VALUES (?,?,?,?,?)')->execute([mb_substr($nombreCompleto, 0, 80), $nombreCompleto, $email, password_hash($password, PASSWORD_DEFAULT), $rol]);
        flash('Usuario creado.');
      } catch (PDOException $x) { flash('Ese correo ya está registrado.'); }
    }
  } elseif (isset($_POST['guardar_rol']) && $id !== (int)$_SESSION['u']['id']) {
    $rol = $_POST['rol'] ?? '';
    if (!in_array($rol, ['admin', 'bibliotecario'], true)) flash('Rol no válido.');
    else {
      $current = $pdo->prepare('SELECT rol FROM usuarios WHERE id=?'); $current->execute([$id]);
      if ($current->fetchColumn() === 'admin' && $rol !== 'admin' && $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin'")->fetchColumn() < 2) flash('Debe quedar al menos un administrador.');
      else { $pdo->prepare('UPDATE usuarios SET rol=? WHERE id=?')->execute([$rol, $id]); flash('Rol actualizado.'); }
    }
  } elseif (isset($_POST['eliminar']) && $id !== (int)$_SESSION['u']['id']) {
    $current = $pdo->prepare('SELECT rol FROM usuarios WHERE id=?'); $current->execute([$id]);
    if ($current->fetchColumn() === 'admin' && $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin'")->fetchColumn() < 2) flash('Debe quedar al menos un administrador.');
    else { $pdo->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]); flash('Usuario eliminado.'); }
  }
  go('usuarios.php');
}
$usuarios = $pdo->query('SELECT id,nombre_completo,email,rol FROM usuarios ORDER BY nombre_completo')->fetchAll();
head('Usuarios'); ?>
<form method="post" class="panel grid">
  <input type="hidden" name="t" value="<?= csrf() ?>">
  <label>Nombre completo<input name="nombre_completo" maxlength="150" autocomplete="name" required></label>
  <label>Correo<input type="email" name="email" required></label>
  <label>Contraseña<input type="password" name="password" minlength="10" required></label>
  <label>Rol<select name="rol"><option value="bibliotecario">Bibliotecario</option><option value="admin">Admin</option></select></label>
  <div class="actions"><button class="btn" name="crear" value="1">Crear usuario</button></div>
</form>
<div class="wrap"><table>
<tr><th>Nombre completo</th><th>Correo</th><th>Rol</th><th></th></tr>
<?php foreach ($usuarios as $usuario): ?><tr><td><?= e($usuario['nombre_completo']) ?><?= (int)$usuario['id'] === (int)$_SESSION['u']['id'] ? ' (tú)' : '' ?></td><td><?= e($usuario['email']) ?></td><td>
<?php if ((int)$usuario['id'] !== (int)$_SESSION['u']['id']): ?><form method="post" class="row"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $usuario['id'] ?>"><select name="rol"><option value="bibliotecario" <?= $usuario['rol'] === 'bibliotecario' ? 'selected' : '' ?>>Bibliotecario</option><option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option></select><button class="link" name="guardar_rol" value="1">Guardar</button></form><?php else: ?>Admin<?php endif; ?></td><td>
<?php if ((int)$usuario['id'] !== (int)$_SESSION['u']['id']): ?><form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $usuario['id'] ?>"><button class="link danger" name="eliminar" value="1" data-confirm="¿Eliminar a <?= e($usuario['nombre_completo']) ?>?">Eliminar</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</table></div>
<?php foot();