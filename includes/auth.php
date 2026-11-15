<?php
/**
 * Control de sesión y permisos.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funciones.php';

/** Datos del usuario que tiene la sesión abierta. */
function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function esta_autenticado(): bool
{
    return usuario_actual() !== null;
}

/** 1 = Administrador, 2 = Gerente de proyecto, 3 = Miembro del equipo. */
function rol_actual(): int
{
    return (int) (usuario_actual()['rol_id'] ?? 0);
}

function es_admin(): bool
{
    return rol_actual() === 1;
}

function es_gerente(): bool
{
    return rol_actual() === 2;
}

function es_miembro(): bool
{
    return rol_actual() === 3;
}

/** Obliga a iniciar sesión. */
function requerir_login(): void
{
    if (!esta_autenticado()) {
        flash('aviso', 'Inicia sesión para continuar.');
        redirigir('login.php');
    }
}

/** Obliga a tener uno de los roles indicados. */
function requerir_rol(array $roles): void
{
    requerir_login();

    if (!in_array(rol_actual(), $roles, true)) {
        flash('error', 'Tu rol no tiene permiso para entrar en esa sección.');
        redirigir('dashboard.php');
    }
}
