<?php
/**
 * Elimina una tarea. Solo el Gerente de proyecto dueño del proyecto.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([2]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('tareas.php');
}

csrf_verificar();

$id  = (int) ($_POST['id'] ?? 0);
$uid = (int) usuario_actual()['id'];

if ($id <= 0) {
    flash('error', 'No se indicó una tarea válida.');
    redirigir('tareas.php');
}

$consulta = $pdo->prepare(
    'SELECT t.titulo
     FROM tareas t
     INNER JOIN proyectos p ON p.id = t.proyecto_id
     WHERE t.id = ? AND p.gerente_id = ?'
);
$consulta->execute([$id, $uid]);
$tarea = $consulta->fetch();

if (!$tarea) {
    flash('error', 'La tarea no existe o no pertenece a tus proyectos.');
    redirigir('tareas.php');
}

$consulta = $pdo->prepare('DELETE FROM tareas WHERE id = ?');
$consulta->execute([$id]);

flash('exito', 'Tarea «' . $tarea['titulo'] . '» eliminada.');
redirigir('tareas.php');
