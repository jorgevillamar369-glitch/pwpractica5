<?php
/**
 * Elimina un proyecto vacío. Solo Administrador y solo por POST.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('proyectos.php');
}

csrf_verificar();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'No se indicó un proyecto válido.');
    redirigir('proyectos.php');
}

$consulta = $pdo->prepare('SELECT nombre FROM proyectos WHERE id = ?');
$consulta->execute([$id]);
$proyecto = $consulta->fetch();

if (!$proyecto) {
    flash('error', 'El proyecto ya no existe.');
    redirigir('proyectos.php');
}

$consulta = $pdo->prepare('SELECT COUNT(*) FROM tareas WHERE proyecto_id = ?');
$consulta->execute([$id]);
$tareas = (int) $consulta->fetchColumn();

if ($tareas > 0) {
    flash('error', 'No se puede eliminar «' . $proyecto['nombre'] . '»: todavía tiene ' . $tareas . ' tarea(s). Elimínalas o muévelas primero.');
    redirigir('proyectos.php');
}

$consulta = $pdo->prepare('DELETE FROM proyectos WHERE id = ?');
$consulta->execute([$id]);

flash('exito', 'Proyecto «' . $proyecto['nombre'] . '» eliminado.');
redirigir('proyectos.php');
