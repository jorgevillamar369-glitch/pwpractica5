<?php
/**
 * Cabecera común: menú lateral, barra superior y mensajes.
 *
 * Antes de incluir este archivo, cada página define:
 *   $titulo     Título visible de la página.
 *   $seccion    dashboard | proyectos | tareas | usuarios (para marcar el menú).
 *   $subtitulo  Texto pequeño sobre el título (opcional).
 *   $acciones   HTML de botones de la cabecera (opcional).
 */

$u         = usuario_actual();
$mensajes  = obtener_mensajes();
$titulo    = $titulo ?? 'Panel';
$seccion   = $seccion ?? '';
$subtitulo = $subtitulo ?? '';
$acciones  = $acciones ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Pwa5</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body>
<div class="aplicacion">
    <aside class="lateral" id="lateral">
        <a class="marca" href="dashboard.php">
            <span class="marca-sello">P5</span>
            <span class="marca-texto">
                <strong>Pwa5</strong>
                <small>Gestión de tareas</small>
            </span>
        </a>

        <nav class="menu" aria-label="Navegación principal">
            <p class="menu-titulo">Trabajo</p>
            <a class="menu-enlace <?= $seccion === 'dashboard' ? 'activo' : '' ?>" href="dashboard.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span>Panel</span>
            </a>

            <?php if (es_admin() || es_gerente()): ?>
                <a class="menu-enlace <?= $seccion === 'proyectos' ? 'activo' : '' ?>" href="proyectos.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span>Proyectos</span>
                </a>
            <?php endif; ?>

            <a class="menu-enlace <?= $seccion === 'tareas' ? 'activo' : '' ?>" href="tareas.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                <span>Tareas</span>
            </a>

            <?php if (es_admin()): ?>
                <p class="menu-titulo">Administración</p>
                <a class="menu-enlace <?= $seccion === 'usuarios' ? 'activo' : '' ?>" href="usuarios.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Usuarios</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="lateral-pie">
            <div class="usuario-mini">
                <span class="avatar"><?= e(iniciales((string) $u['nombre'])) ?></span>
                <span class="usuario-mini-texto">
                    <strong><?= e($u['nombre']) ?></strong>
                    <small><?= e($u['rol_nombre']) ?></small>
                </span>
            </div>
            <a class="salir" href="logout.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <div class="velo" id="velo"></div>

    <div class="principal">
        <header class="cabecera">
            <button class="menu-boton" id="menuBoton" type="button" aria-label="Abrir menú">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <div class="cabecera-texto">
                <?php if ($subtitulo !== ''): ?>
                    <p class="sobretitulo"><?= e($subtitulo) ?></p>
                <?php endif; ?>
                <h1><?= e($titulo) ?></h1>
            </div>

            <?php if ($acciones !== ''): ?>
                <div class="cabecera-acciones"><?= $acciones ?></div>
            <?php endif; ?>
        </header>

        <main class="contenido">
            <?php foreach ($mensajes as $mensaje): ?>
                <div class="aviso aviso-<?= e($mensaje['tipo']) ?>" role="status">
                    <span><?= e($mensaje['texto']) ?></span>
                </div>
            <?php endforeach; ?>
