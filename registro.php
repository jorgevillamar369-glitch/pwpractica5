<?php
/**
 * Registro público de nuevas cuentas.
 * Toda cuenta creada aquí entra con el rol Miembro del equipo.
 */

require_once __DIR__ . '/includes/auth.php';

if (esta_autenticado()) {
    redirigir('dashboard.php');
}

$datos = ['nombre' => '', 'email' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $datos = [
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'email'  => trim((string) ($_POST['email'] ?? '')),
    ];
    $clave     = (string) ($_POST['clave'] ?? '');
    $confirmar = (string) ($_POST['clave_confirmar'] ?? '');

    if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 50) {
        $errores['nombre'] = 'El nombre debe tener entre 3 y 50 caracteres.';
    }

    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 100) {
        $errores['email'] = 'Escribe un correo válido de hasta 100 caracteres.';
    }

    if (empty($errores['email'])) {
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $consulta->execute([$datos['email']]);

        if ($consulta->fetch()) {
            $errores['email'] = 'Ya existe una cuenta con ese correo.';
        }
    }

    if (mb_strlen($clave) < 8) {
        $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (mb_strlen($clave) > 72) {
        $errores['clave'] = 'La contraseña no puede pasar de 72 caracteres.';
    } elseif ($clave !== $confirmar) {
        $errores['clave'] = 'Las dos contraseñas no coinciden.';
    }

    if (empty($errores)) {
        try {
            $consulta = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, `contraseña`, rol_id) VALUES (?, ?, ?, 3)'
            );
            $consulta->execute([
                $datos['nombre'],
                $datos['email'],
                password_hash($clave, PASSWORD_DEFAULT),
            ]);

            flash('exito', 'Cuenta creada. Ya puedes iniciar sesión con tu correo.');
            redirigir('login.php');
        } catch (PDOException $error) {
            error_log('Error al registrar la cuenta: ' . $error->getMessage());
            $errores['general'] = 'No se pudo crear la cuenta. Inténtalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta · Pwa5</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body class="cuerpo-login">
<div class="login">
    <aside class="login-foto">
        <a class="marca marca-clara" href="login.php">
            <span class="marca-sello">P5</span>
            <span class="marca-texto">
                <strong>Pwa5</strong>
                <small>Gestión de tareas</small>
            </span>
        </a>

        <div class="login-discurso">
            <p class="sobretitulo">Práctica 5 · Programación Web Avanzada</p>
            <p class="login-frase">Crea tu cuenta y coordina el trabajo del equipo.</p>
            <p class="login-creditos">© 2026 Grupo 04 · Jorge Villamar · Norka Mideros · Bruce Balarezo · Mauro Chinlle · Liseth Criollo</p>
            <p class="login-credito">Fotografía: Pexels</p>
        </div>
    </aside>

    <main class="login-panel">
        <div class="login-caja">
            <div class="login-marca-movil">
                <span class="marca-sello">P5</span>
                <span class="marca-texto">
                    <strong>Pwa5</strong>
                    <small>Gestión de tareas</small>
                </span>
            </div>

            <p class="creditos-movil">© 2026 Grupo 04 · Jorge Villamar · Norka Mideros · Bruce Balarezo · Mauro Chinlle · Liseth Criollo</p>

            <p class="sobretitulo">Registro de cuenta</p>
            <h1>Crear cuenta</h1>
            <p class="login-intro">
                Completa tus datos. La cuenta se crea con el rol Miembro del equipo;
                un administrador puede cambiarlo después.
            </p>

            <div class="aviso aviso-aviso" role="note">
                <span>
                    <strong>No uses datos reales.</strong> Este es un sitio de práctica:
                    crea la cuenta con un correo de prueba y una contraseña distinta
                    a la que usas en Google, tu correo personal o cualquier otro servicio.
                    Si solo quieres probar, <a href="login.php#cuentas-demo">usa una cuenta de demostración</a>.
                </span>
            </div>

            <?php if (isset($errores['general'])): ?>
                <div class="aviso aviso-error" role="alert">
                    <span><?= e($errores['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" class="formulario-login">
                <?= csrf_campo() ?>

                <div class="campo">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                           id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>"
                           maxlength="50" autocomplete="name" required autofocus>
                    <?php if (isset($errores['nombre'])): ?>
                        <p class="error-campo"><?= e($errores['nombre']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="email">Correo</label>
                    <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= e($datos['email']) ?>"
                           maxlength="100" autocomplete="email" required>
                    <?php if (isset($errores['email'])): ?>
                        <p class="error-campo"><?= e($errores['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>
                    <div class="campo-clave">
                        <input type="password" class="form-control <?= isset($errores['clave']) ? 'is-invalid' : '' ?>"
                               id="clave" name="clave" autocomplete="new-password" required>
                        <button type="button" class="ver-clave" data-objetivo="clave" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <p class="ayuda-campo">Mínimo 8 caracteres.</p>
                    <?php if (isset($errores['clave'])): ?>
                        <p class="error-campo"><?= e($errores['clave']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="clave_confirmar">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="clave_confirmar" name="clave_confirmar"
                           autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn btn-primario btn-ancho">Crear cuenta</button>
            </form>

            <p class="login-cambio">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
        </div>
    </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
