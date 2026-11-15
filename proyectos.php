<?php
/**
 * Proyectos. El Administrador ve todos; el Gerente solo los suyos.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1, 2]);

$u   = usuario_actual();
$rol = (int) $u['rol_id'];

$sql = 'SELECT p.id, p.nombre, p.descripcion, p.creado_en, u.nombre AS gerente,
               (SELECT COUNT(*) FROM tareas t WHERE t.proyecto_id = p.id) AS total,
               (SELECT COUNT(*) FROM tareas t WHERE t.proyecto_id = p.id AND t.estado = \'Pendiente\') AS pendientes,
               (SELECT COUNT(*) FROM tareas t WHERE t.proyecto_id = p.id AND t.estado = \'En proceso\') AS en_proceso,
               (SELECT COUNT(*) FROM tareas t WHERE t.proyecto_id = p.id AND t.estado = \'Completado\') AS completadas
        FROM proyectos p
        INNER JOIN usuarios u ON u.id = p.gerente_id';

$parametros = [];
if ($rol === 2) {
    $sql .= ' WHERE p.gerente_id = ?';
    $parametros[] = (int) $u['id'];
}

$sql .= ' ORDER BY p.nombre';

$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$proyectos = $consulta->fetchAll();

$titulo    = 'Proyectos';
$seccion   = 'proyectos';
$subtitulo = $rol === 1 ? 'Todos los proyectos' : 'Proyectos a tu cargo';
$acciones  = $rol === 1
    ? '<a class="btn btn-primario" href="proyecto_form.php">Nuevo proyecto</a>'
    : '<a class="btn btn-primario" href="tarea_form.php">Nueva tarea</a>';

require __DIR__ . '/includes/header.php';
?>

<section class="panel panel-intro">
    <div class="panel-cuerpo panel-cuerpo-fila">
        <div>
            <p class="sobretitulo">Organización</p>
            <h2><?= count($proyectos) ?> <?= count($proyectos) === 1 ? 'proyecto activo' : 'proyectos activos' ?></h2>
            <p class="panel-nota">
                <?= $rol === 1
                    ? 'Cada proyecto tiene un gerente responsable de sus tareas y de su equipo.'
                    : 'Solo aparecen los proyectos que tienes asignados como gerente.' ?>
            </p>
        </div>
    </div>
</section>

<?php if (empty($proyectos)): ?>
    <section class="panel">
        <div class="estado-vacio">
            <p class="estado-vacio-titulo">No hay proyectos para mostrar.</p>
            <p class="estado-vacio-texto">
                <?= $rol === 1
                    ? 'Crea el primer proyecto y asígnale un gerente para empezar.'
                    : 'El administrador todavía no te ha asignado un proyecto.' ?>
            </p>
            <?php if ($rol === 1): ?>
                <a class="btn btn-primario" href="proyecto_form.php">Crear proyecto</a>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="rejilla-proyectos">
        <?php foreach ($proyectos as $indice => $proyecto): ?>
            <?php
            $total       = (int) $proyecto['total'];
            $completadas = (int) $proyecto['completadas'];
            $porcentaje  = $total > 0 ? (int) round($completadas * 100 / $total) : 0;

            if ($total === 0) {
                $etiquetaProyecto = 'Sin tareas';
                $claseProyecto    = 'estado-pendiente';
            } elseif ($completadas === $total) {
                $etiquetaProyecto = 'Completado';
                $claseProyecto    = 'estado-completado';
            } else {
                $etiquetaProyecto = 'En curso';
                $claseProyecto    = 'estado-proceso';
            }
            ?>
            <article class="tarjeta-proyecto">
                <header class="tarjeta-proyecto-cabecera">
                    <span class="proyecto-indice"><?= str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="pildora <?= e($claseProyecto) ?>"><?= e($etiquetaProyecto) ?></span>
                </header>

                <h2><?= e($proyecto['nombre']) ?></h2>
                <p class="tarjeta-proyecto-descripcion">
                    <?= e($proyecto['descripcion'] !== null && $proyecto['descripcion'] !== '' ? $proyecto['descripcion'] : 'Sin descripción registrada.') ?>
                </p>

                <dl class="datos-proyecto">
                    <div>
                        <dt>Gerente</dt>
                        <dd><?= e($proyecto['gerente']) ?></dd>
                    </div>
                    <div>
                        <dt>Tareas</dt>
                        <dd><?= $total ?></dd>
                    </div>
                </dl>

                <div class="progreso" role="img" aria-label="<?= $porcentaje ?>% de tareas completadas">
                    <span style="width: <?= $porcentaje ?>%"></span>
                </div>
                <div class="progreso-texto">
                    <span><?= $completadas ?> de <?= $total ?> completadas</span>
                    <strong><?= $porcentaje ?>%</strong>
                </div>

                <div class="resumen-estados">
                    <span><i class="punto punto-pendiente"></i><?= (int) $proyecto['pendientes'] ?> <?= (int) $proyecto['pendientes'] === 1 ? 'pendiente' : 'pendientes' ?></span>
                    <span><i class="punto punto-proceso"></i><?= (int) $proyecto['en_proceso'] ?> en proceso</span>
                    <span><i class="punto punto-completado"></i><?= $completadas ?> <?= $completadas === 1 ? 'completada' : 'completadas' ?></span>
                </div>

                <footer class="tarjeta-proyecto-pie">
                    <a class="enlace-flecha" href="tareas.php?proyecto=<?= (int) $proyecto['id'] ?>">Ver tareas</a>

                    <?php if ($rol === 1): ?>
                        <div class="acciones-fila">
                            <a class="accion" href="proyecto_form.php?id=<?= (int) $proyecto['id'] ?>">Editar</a>
                            <button type="button" class="accion accion-peligro js-eliminar"
                                    data-url="proyecto_eliminar.php"
                                    data-id="<?= (int) $proyecto['id'] ?>"
                                    data-nombre="<?= e($proyecto['nombre']) ?>">
                                Eliminar
                            </button>
                        </div>
                    <?php else: ?>
                        <a class="accion" href="tarea_form.php?proyecto=<?= (int) $proyecto['id'] ?>">Añadir tarea</a>
                    <?php endif; ?>
                </footer>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
