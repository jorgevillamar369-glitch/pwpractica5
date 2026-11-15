<?php
/**
 * Punto de entrada: manda al panel o al inicio de sesión.
 */

require_once __DIR__ . '/includes/auth.php';

redirigir(esta_autenticado() ? 'dashboard.php' : 'login.php');
