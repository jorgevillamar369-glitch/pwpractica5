<?php
/**
 * Crear o editar un usuario. Solo para el Administrador.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1]);

$u   = usuario_actual();
$id  = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$roles = $pdo->query('SELECT rol_id, rol_nombre FROM roles ORDER BY rol_id')->fetchAll();

$datos = ['nombre' => '', 'email' => '', 'rol_id' => 3];
$errores = [];
$editando = false;

if ($id > 0) {
    $consulta = $pdo->prepare('SELECT id, nombre, email, rol_id FROM usuarios WHERE id = ?');
    $consulta->execute([$id]);
    $usuario = $consulta->fetch();

    if (!$usuario) {
        flash('error', 'El usuario que buscas no existe.');
        redirigir('usuarios.php');
    }

    $editando = true;
    $datos = [
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'rol_id' => (int) $usuario['rol_id'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $datos = [
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'email'  => trim((string) ($_POST['email'] ?? '')),
        'rol_id' => (int) ($_POST['rol_id'] ?? 0),
    ];
    $clave     = (string) ($_POST['clave'] ?? '');
    $confirmar = (string) ($_POST['clave_confirmar'] ?? '');

    if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 50) {
        $errores['nombre'] = 'El nombre debe tener entre 3 y 50 caracteres.';
    }

    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 100) {
        $errores['email'] = 'Escribe un correo válido de hasta 100 caracteres.';
    }

    $rolesValidos = array_map(static fn($rol) => (int) $rol['rol_id'], $roles);
    if (!in_array($datos['rol_id'], $rolesValidos, true)) {
        $errores['rol_id'] = 'Selecciona un rol válido.';
    }

    if (empty($errores['email'])) {
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $consulta->execute([$datos['email'], $id]);

        if ($consulta->fetch()) {
            $errores['email'] = 'Ya existe una cuenta con ese correo.';
        }
    }

    if (!$editando || $clave !== '') {
        if (mb_strlen($clave) < 8) {
            $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (mb_strlen($clave) > 72) {
            $errores['clave'] = 'La contraseña no puede pasar de 72 caracteres.';
        } elseif ($clave !== $confirmar) {
            $errores['clave'] = 'Las dos contraseñas no coinciden.';
        }
    }

    if ($editando && $id === (int) $u['id'] && $datos['rol_id'] !== (int) $u['rol_id']) {
        $errores['rol_id'] = 'No puedes cambiar tu propio rol. Pídeselo a otro administrador.';
    }

    if (empty($errores)) {
        if ($editando) {
            if ($clave !== '') {
                $consulta = $pdo->prepare(
                    'UPDATE usuarios SET nombre = ?, email = ?, rol_id = ?, `contraseña` = ? WHERE id = ?'
                );
                $consulta->execute([
                    $datos['nombre'],
                    $datos['email'],
                    $datos['rol_id'],
                    password_hash($clave, PASSWORD_DEFAULT),
                    $id,
                ]);
            } else {
                $consulta = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, rol_id = ? WHERE id = ?');
                $consulta->execute([$datos['nombre'], $datos['email'], $datos['rol_id'], $id]);
            }

            if ($id === (int) $u['id']) {
                $_SESSION['usuario']['nombre'] = $datos['nombre'];
                $_SESSION['usuario']['email']  = $datos['email'];
            }

            flash('exito', 'Usuario actualizado correctamente.');
        } else {
            $consulta = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, `contraseña`, rol_id) VALUES (?, ?, ?, ?)'
            );
            $consulta->execute([
                $datos['nombre'],
                $datos['email'],
                password_hash($clave, PASSWORD_DEFAULT),
                $datos['rol_id'],
            ]);

            flash('exito', 'Usuario creado. Ya puede iniciar sesión con su correo.');
        }

        redirigir('usuarios.php');
    }
}

$esMiCuenta = $editando && $id === (int) $u['id'];

$titulo    = $editando ? 'Editar usuario' : 'Nuevo usuario';
$seccion   = 'usuarios';
$subtitulo = 'Cuentas y roles';
$acciones  = '<a class="btn btn-contorno" href="usuarios.php">Volver al listado</a>';

require __DIR__ . '/includes/header.php';
?>

<form method="post" class="formulario" action="usuario_form.php<?= $editando ? '?id=' . $id : '' ?>" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <?php if (!empty($errores)): ?>
        <div class="aviso aviso-error" role="alert">
            <span>Revisa los campos marcados antes de guardar.</span>
        </div>
    <?php endif; ?>

    <section class="panel">
        <header class="panel-cabecera">
            <div>
                <p class="sobretitulo">Datos de la cuenta</p>
                <h2><?= $editando ? 'Información personal' : 'Alta de usuario' ?></h2>
            </div>
        </header>

        <div class="panel-cuerpo">
            <div class="rejilla-formulario">
                <div class="campo">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                           id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>"
                           maxlength="50" required>
                    <?php if (isset($errores['nombre'])): ?>
                        <p class="error-campo"><?= e($errores['nombre']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="email">Correo</label>
                    <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= e($datos['email']) ?>"
                           maxlength="100" required>
                    <?php if (isset($errores['email'])): ?>
                        <p class="error-campo"><?= e($errores['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="campo">
                <label for="rol_id">Rol</label>
                <select class="form-select <?= isset($errores['rol_id']) ? 'is-invalid' : '' ?>"
                        id="rol_id" name="rol_id" <?= $esMiCuenta ? 'disabled' : '' ?>>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= (int) $rol['rol_id'] ?>" <?= (int) $rol['rol_id'] === (int) $datos['rol_id'] ? 'selected' : '' ?>>
                            <?= e($rol['rol_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($esMiCuenta): ?>
                    <input type="hidden" name="rol_id" value="<?= (int) $datos['rol_id'] ?>">
                    <p class="ayuda-campo">No puedes cambiar tu propio rol.</p>
                <?php endif; ?>
                <?php if (isset($errores['rol_id'])): ?>
                    <p class="error-campo"><?= e($errores['rol_id']) ?></p>
                <?php endif; ?>
            </div>

            <div class="rejilla-formulario">
                <div class="campo">
                    <label for="clave"><?= $editando ? 'Nueva contraseña' : 'Contraseña' ?></label>
                    <div class="campo-clave">
                        <input type="password" class="form-control <?= isset($errores['clave']) ? 'is-invalid' : '' ?>"
                               id="clave" name="clave" <?= $editando ? '' : 'required' ?> autocomplete="new-password">
                        <button type="button" class="ver-clave" data-objetivo="clave" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <p class="ayuda-campo">
                        <?= $editando ? 'Déjala vacía para conservar la contraseña actual.' : 'Mínimo 8 caracteres.' ?>
                    </p>
                </div>

                <div class="campo">
                    <label for="clave_confirmar">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="clave_confirmar" name="clave_confirmar"
                           autocomplete="new-password">
                </div>
            </div>

            <?php if (isset($errores['clave'])): ?>
                <p class="error-campo"><?= e($errores['clave']) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <div class="formulario-acciones">
        <button type="submit" class="btn btn-primario"><?= $editando ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <a class="btn btn-contorno" href="usuarios.php">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
