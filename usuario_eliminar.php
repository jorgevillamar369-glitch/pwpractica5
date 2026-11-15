<?php
/**
 * Elimina un usuario. Solo Administrador y solo por POST.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('usuarios.php');
}

csrf_verificar();

$id = (int) ($_POST['id'] ?? 0);
$u  = usuario_actual();

if ($id <= 0) {
    flash('error', 'No se indicó un usuario válido.');
    redirigir('usuarios.php');
}

if ($id === (int) $u['id']) {
    flash('error', 'No puedes eliminar tu propia cuenta.');
    redirigir('usuarios.php');
}

$consulta = $pdo->prepare('SELECT nombre FROM usuarios WHERE id = ?');
$consulta->execute([$id]);
$usuario = $consulta->fetch();

if (!$usuario) {
    flash('error', 'El usuario ya no existe.');
    redirigir('usuarios.php');
}

$consulta = $pdo->prepare('SELECT COUNT(*) FROM tareas WHERE usuario_id = ?');
$consulta->execute([$id]);
$tareasAsignadas = (int) $consulta->fetchColumn();

$consulta = $pdo->prepare('SELECT COUNT(*) FROM proyectos WHERE gerente_id = ?');
$consulta->execute([$id]);
$proyectosACargo = (int) $consulta->fetchColumn();

if ($tareasAsignadas > 0 || $proyectosACargo > 0) {
    $detalle = [];
    if ($tareasAsignadas > 0) {
        $detalle[] = $tareasAsignadas . ' tarea(s) asignada(s)';
    }
    if ($proyectosACargo > 0) {
        $detalle[] = $proyectosACargo . ' proyecto(s) a cargo';
    }

    flash('error', 'No se puede eliminar a ' . $usuario['nombre'] . ': tiene ' . implode(' y ', $detalle) . '.');
    redirigir('usuarios.php');
}

$consulta = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
$consulta->execute([$id]);

flash('exito', 'Usuario ' . $usuario['nombre'] . ' eliminado.');
redirigir('usuarios.php');
