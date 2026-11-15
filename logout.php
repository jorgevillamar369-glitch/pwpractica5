<?php
/**
 * Cierra la sesión y vuelve al inicio de sesión.
 */

require_once __DIR__ . '/includes/auth.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        (bool) $parametros['secure'],
        (bool) $parametros['httponly']
    );
}

session_destroy();
session_start();
flash('exito', 'La sesión se cerró correctamente.');

redirigir('login.php');
