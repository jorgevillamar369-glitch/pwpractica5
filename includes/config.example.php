<?php
/**
 * Plantilla de configuración.
 *
 * Copia este archivo como config.php y completa los datos de tu servidor.
 * En Laragon la base local usa el puerto 3307; en la mayoría de hospedajes
 * el puerto es 3306.
 */

declare(strict_types=1);

const APP_NOMBRE = 'Pwa5 · Gestión de tareas';
const APP_DEBUG = false; // Nunca dejar true en un sitio público.

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NOMBRE = 'nombre_de_la_base';
const DB_USUARIO = 'usuario_de_la_base';
const DB_CLAVE = 'contraseña_de_la_base';

date_default_timezone_set('America/Guayaquil');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NOMBRE . ';charset=utf8mb4',
        DB_USUARIO,
        DB_CLAVE,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $error) {
    error_log('Error de conexión: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Revisa includes/config.php.');
}
