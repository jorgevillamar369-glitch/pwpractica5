<?php
/**
 * Inicio de sesión.
 */

require_once __DIR__ . '/includes/auth.php';

if (esta_autenticado()) {
    redirigir('dashboard.php');
}

$email = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_email']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $email = trim((string) ($_POST['email'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');

    if ($email === '' || $clave === '') {
        flash('error', 'Escribe tu correo y tu contraseña.');
    } else {
        $consulta = $pdo->prepare(
            'SELECT u.id, u.nombre, u.email, u.`contraseña`, u.rol_id, r.rol_nombre
             FROM usuarios u
             INNER JOIN roles r ON r.rol_id = u.rol_id
             WHERE u.email = ?
             LIMIT 1'
        );
        $consulta->execute([$email]);
        $usuario = $consulta->fetch();

        if ($usuario && password_verify($clave, $usuario['contraseña'])) {
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id'         => (int) $usuario['id'],
                'nombre'     => $usuario['nombre'],
                'email'      => $usuario['email'],
                'rol_id'     => (int) $usuario['rol_id'],
                'rol_nombre' => $usuario['rol_nombre'],
            ];

            flash('exito', 'Sesión iniciada. Hola, ' . $usuario['nombre'] . '.');
            redirigir('dashboard.php');
        }

        flash('error', 'El correo o la contraseña no son correctos.');
    }

    $_SESSION['login_email'] = $email;
    redirigir('login.php');
}

$mensajes = obtener_mensajes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Pwa5</title>
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
            <p class="login-frase">Solicitudes, responsables y estados en una sola vista.</p>
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

            <p class="sobretitulo">Acceso al sistema</p>
            <h1>Gestión de tareas por equipos</h1>
            <p class="login-intro">Entra con el correo y la contraseña de tu cuenta.</p>

            <div class="aviso aviso-aviso" role="note">
                <span>
                    Sitio de práctica: no escribas un correo ni una contraseña
                    que uses en Google u otros servicios. Si solo quieres probar,
                    <a href="#cuentas-demo">usa una cuenta de demostración</a>.
                </span>
            </div>

            <?php foreach ($mensajes as $mensaje): ?>
                <div class="aviso aviso-<?= e($mensaje['tipo']) ?>" role="status">
                    <span><?= e($mensaje['texto']) ?></span>
                </div>
            <?php endforeach; ?>

            <form method="post" class="formulario-login">
                <?= csrf_campo() ?>

                <div class="campo">
                    <label for="email">Correo</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e($email) ?>" autocomplete="username" required autofocus>
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>
                    <div class="campo-clave">
                        <input type="password" class="form-control" id="clave" name="clave"
                               autocomplete="current-password" required>
                        <button type="button" class="ver-clave" data-objetivo="clave" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primario btn-ancho">Entrar</button>
            </form>

            <p class="login-cambio">¿No tienes cuenta? <a href="registro.php">Crear una cuenta</a></p>

            <section class="cuentas-demo" id="cuentas-demo" aria-labelledby="titulo-demo">
                <h2 class="cuentas-demo-titulo" id="titulo-demo">Cuentas de demostración</h2>
                <dl>
                    <div><dt>Administrador</dt><dd><code>admin@ejemplo.com</code> · <code>Admin123!</code></dd></div>
                    <div><dt>Gerente</dt><dd><code>gerente@ejemplo.com</code> · <code>Gerente123!</code></dd></div>
                    <div><dt>Miembro</dt><dd><code>miembro@ejemplo.com</code> · <code>Miembro123!</code></dd></div>
                </dl>
            </section>
        </div>
    </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
