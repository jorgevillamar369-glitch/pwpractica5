<?php
/**
 * Actualiza el estado de una tarea.
 * El Miembro solo puede cambiar las suyas; el Gerente, las de sus proyectos.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('tareas.php');
}

csrf_verificar();

$id     = (int) ($_POST['id'] ?? 0);
$estado = (string) ($_POST['estado'] ?? '');
$u      = usuario_actual();
$rol    = (int) $u['rol_id'];
$uid    = (int) $u['id'];

if ($id <= 0 || !in_array($estado, estados_validos(), true)) {
    flash('error', 'Los datos enviados no son válidos.');
    redirigir('tareas.php');
}

if ($rol === 3) {
    $consulta = $pdo->prepare('SELECT titulo FROM tareas WHERE id = ? AND usuario_id = ?');
    $consulta->execute([$id, $uid]);
} elseif ($rol === 2) {
    $consulta = $pdo->prepare(
        'SELECT t.titulo
         FROM tareas t
         INNER JOIN proyectos p ON p.id = t.proyecto_id
         WHERE t.id = ? AND p.gerente_id = ?'
    );
    $consulta->execute([$id, $uid]);
} else {
    flash('error', 'El Administrador solo puede consultar las tareas.');
    redirigir('tareas.php');
}

$tarea = $consulta->fetch();

if (!$tarea) {
    flash('error', 'No puedes cambiar el estado de esa tarea.');
    redirigir('tareas.php');
}

$consulta = $pdo->prepare('UPDATE tareas SET estado = ? WHERE id = ?');
$consulta->execute([$estado, $id]);

flash('exito', 'La tarea «' . $tarea['titulo'] . '» quedó en estado «' . $estado . '».');
redirigir('tareas.php');
