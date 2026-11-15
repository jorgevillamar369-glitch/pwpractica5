<?php
/**
 * Funciones de apoyo usadas en todas las páginas.
 */

declare(strict_types=1);

/** Escapa texto antes de imprimirlo en HTML. */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Envía al navegador a otra página y detiene el script. */
function redirigir(string $ruta): void
{
    header('Location: ' . $ruta);
    exit;
}

/** Devuelve el token CSRF de la sesión (lo crea la primera vez). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Campo oculto para incluir en los formularios. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Detiene la petición si el token no coincide. */
function csrf_verificar(): void
{
    $enviado = $_POST['csrf_token'] ?? '';

    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(403);
        exit('La solicitud expiró. Vuelve atrás y recarga la página.');
    }
}

/** Guarda un mensaje para mostrarlo después de una redirección. */
function flash(string $tipo, string $texto): void
{
    $_SESSION['mensajes'][] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Devuelve y vacía los mensajes pendientes. */
function obtener_mensajes(): array
{
    $mensajes = $_SESSION['mensajes'] ?? [];
    unset($_SESSION['mensajes']);

    return $mensajes;
}

/** Fecha de hoy escrita en español, por ejemplo: "Sábado, 14 de noviembre de 2026". */
function fecha_larga(): string
{
    $dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    $hoy = new DateTimeImmutable('now');
    $dia = $dias[(int) $hoy->format('w')];
    $mes = $meses[(int) $hoy->format('n')];

    return ucfirst($dia) . ', ' . (int) $hoy->format('j') . ' de ' . $mes . ' de ' . $hoy->format('Y');
}

/** Primeras letras del nombre para el avatar, por ejemplo: "MP". */
function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre)) ?: [];
    $letras = '';

    foreach (array_slice($partes, 0, 2) as $parte) {
        $letras .= mb_substr($parte, 0, 1);
    }

    return mb_strtoupper($letras);
}

/** Estados permitidos por el enunciado. */
function estados_validos(): array
{
    return ['Pendiente', 'En proceso', 'Completado'];
}

/** Clase CSS que corresponde a cada estado. */
function clase_estado(string $estado): string
{
    switch ($estado) {
        case 'En proceso':
            return 'estado-proceso';
        case 'Completado':
            return 'estado-completado';
        default:
            return 'estado-pendiente';
    }
}

/** Fecha corta para las tablas. */
function fecha_corta($fecha): string
{
    if (empty($fecha)) {
        return '—';
    }

    $marca = strtotime((string) $fecha);

    return $marca ? date('d/m/Y', $marca) : '—';
}
