<?php
/**
 * Panel principal. Cambia según el rol del usuario.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_login();

$u   = usuario_actual();
$rol = (int) $u['rol_id'];
$uid = (int) $u['id'];

$hora   = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$primerNombre = explode(' ', trim((string) $u['nombre']))[0];

$desglose = ['Pendiente' => 0, 'En proceso' => 0, 'Completado' => 0];
$tareas   = [];
$stats    = [];
$mensajeRol = '';
$enlaceNota = 'tareas.php';
$textoNota  = '';

if ($rol === 1) {
    $totalUsuarios  = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $totalProyectos = (int) $pdo->query('SELECT COUNT(*) FROM proyectos')->fetchColumn();

    foreach ($pdo->query('SELECT estado, COUNT(*) AS total FROM tareas GROUP BY estado') as $fila) {
        $desglose[$fila['estado']] = (int) $fila['total'];
    }

    $abiertas   = $desglose['Pendiente'] + $desglose['En proceso'];
    $completadas = $desglose['Completado'];
    $totalTareas = $abiertas + $completadas;
    $porcentaje  = $totalTareas > 0 ? (int) round($completadas * 100 / $totalTareas) : 0;

    $stats = [
        ['indice' => '01', 'etiqueta' => 'Usuarios', 'numero' => $totalUsuarios, 'nota' => 'Los tres roles del sistema'],
        ['indice' => '02', 'etiqueta' => 'Proyectos', 'numero' => $totalProyectos, 'nota' => 'Cada uno con un gerente'],
        ['indice' => '03', 'etiqueta' => 'Tareas abiertas', 'numero' => $abiertas, 'nota' => 'Pendientes o en proceso'],
        ['indice' => '04', 'etiqueta' => 'Completadas', 'numero' => $completadas, 'nota' => $porcentaje . '% del total'],
    ];

    $tareas = $pdo->query(
        'SELECT t.id, t.titulo, t.estado, t.actualizado_en, p.nombre AS proyecto, us.nombre AS responsable
         FROM tareas t
         INNER JOIN proyectos p ON p.id = t.proyecto_id
         INNER JOIN usuarios us ON us.id = t.usuario_id
         ORDER BY t.actualizado_en DESC
         LIMIT 6'
    )->fetchAll();

    $mensajeRol = 'Tienes la vista completa del sistema: usuarios, proyectos y tareas.';
    $textoNota  = 'Revisa que cada proyecto tenga un gerente y que los roles estén bien asignados.';
    $enlaceNota = 'usuarios.php';
    $acciones   = '<a class="btn btn-contorno" href="usuarios.php">Gestionar usuarios</a>';
} elseif ($rol === 2) {
    $consulta = $pdo->prepare('SELECT COUNT(*) FROM proyectos WHERE gerente_id = ?');
    $consulta->execute([$uid]);
    $totalProyectos = (int) $consulta->fetchColumn();

    $consulta = $pdo->prepare(
        'SELECT t.estado, COUNT(*) AS total
         FROM tareas t
         INNER JOIN proyectos p ON p.id = t.proyecto_id
         WHERE p.gerente_id = ?
         GROUP BY t.estado'
    );
    $consulta->execute([$uid]);

    foreach ($consulta as $fila) {
        $desglose[$fila['estado']] = (int) $fila['total'];
    }

    $totalTareas = array_sum($desglose);
    $porcentaje  = $totalTareas > 0 ? (int) round($desglose['Completado'] * 100 / $totalTareas) : 0;

    $stats = [
        ['indice' => '01', 'etiqueta' => 'Mis proyectos', 'numero' => $totalProyectos, 'nota' => 'A tu cargo'],
        ['indice' => '02', 'etiqueta' => 'Pendientes', 'numero' => $desglose['Pendiente'], 'nota' => 'Sin empezar'],
        ['indice' => '03', 'etiqueta' => 'En proceso', 'numero' => $desglose['En proceso'], 'nota' => 'Con avance'],
        ['indice' => '04', 'etiqueta' => 'Completadas', 'numero' => $desglose['Completado'], 'nota' => $porcentaje . '% del total'],
    ];

    $consulta = $pdo->prepare(
        'SELECT t.id, t.titulo, t.estado, t.actualizado_en, p.nombre AS proyecto, us.nombre AS responsable
         FROM tareas t
         INNER JOIN proyectos p ON p.id = t.proyecto_id
         INNER JOIN usuarios us ON us.id = t.usuario_id
         WHERE p.gerente_id = ?
         ORDER BY t.actualizado_en DESC
         LIMIT 6'
    );
    $consulta->execute([$uid]);
    $tareas = $consulta->fetchAll();

    $mensajeRol = 'Revisa el avance de tus proyectos y ajusta lo que esté detenido.';
    $textoNota  = 'Crea una tarea nueva o cambia el responsable de las que siguen pendientes.';
    $enlaceNota = 'tarea_form.php';
    $acciones   = '<a class="btn btn-primario" href="tarea_form.php">Nueva tarea</a>';
} else {
    $consulta = $pdo->prepare(
        'SELECT estado, COUNT(*) AS total FROM tareas WHERE usuario_id = ? GROUP BY estado'
    );
    $consulta->execute([$uid]);

    foreach ($consulta as $fila) {
        $desglose[$fila['estado']] = (int) $fila['total'];
    }

    $totalTareas = array_sum($desglose);

    $stats = [
        ['indice' => '01', 'etiqueta' => 'Asignadas', 'numero' => $totalTareas, 'nota' => 'En total'],
        ['indice' => '02', 'etiqueta' => 'Pendientes', 'numero' => $desglose['Pendiente'], 'nota' => 'Sin empezar'],
        ['indice' => '03', 'etiqueta' => 'En proceso', 'numero' => $desglose['En proceso'], 'nota' => 'Con avance'],
        ['indice' => '04', 'etiqueta' => 'Completadas', 'numero' => $desglose['Completado'], 'nota' => 'Cerradas'],
    ];

    $consulta = $pdo->prepare(
        'SELECT t.id, t.titulo, t.estado, t.actualizado_en, p.nombre AS proyecto, us.nombre AS responsable
         FROM tareas t
         INNER JOIN proyectos p ON p.id = t.proyecto_id
         INNER JOIN usuarios us ON us.id = t.usuario_id
         WHERE t.usuario_id = ?
         ORDER BY t.actualizado_en DESC
         LIMIT 6'
    );
    $consulta->execute([$uid]);
    $tareas = $consulta->fetchAll();

    $mensajeRol = 'Estas son las tareas que tienes asignadas. Actualiza el estado cuando avances.';
    $textoNota  = 'Abre tus tareas y marca como «En proceso» la que ya empezaste.';
    $enlaceNota = 'tareas.php';
}

$totalTareas = array_sum($desglose);
$clasesBarra = ['Pendiente' => 'barra-pendiente', 'En proceso' => 'barra-proceso', 'Completado' => 'barra-completado'];

$titulo    = 'Panel';
$seccion   = 'dashboard';
$subtitulo = 'Resumen de trabajo';

require __DIR__ . '/includes/header.php';
?>

<section class="saludo">
    <div class="saludo-texto">
        <p class="sobretitulo"><?= e(fecha_larga()) ?></p>
        <h2><?= e($saludo) ?>, <?= e($primerNombre) ?>.</h2>
        <p class="saludo-descripcion"><?= e($mensajeRol) ?></p>
    </div>
    <div class="saludo-marca" aria-hidden="true">P5</div>
</section>

<section class="rejilla-stats" aria-label="Indicadores del sistema">
    <?php foreach ($stats as $stat): ?>
        <article class="tarjeta-stat">
            <span class="stat-indice"><?= e($stat['indice']) ?></span>
            <span class="stat-etiqueta"><?= e($stat['etiqueta']) ?></span>
            <strong class="stat-numero"><?= e((string) $stat['numero']) ?></strong>
            <span class="stat-nota"><?= e($stat['nota']) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<div class="rejilla-panel">
    <section class="panel">
        <header class="panel-cabecera">
            <div>
                <p class="sobretitulo">Actividad</p>
                <h2>Tareas recientes</h2>
            </div>
            <a class="enlace-flecha" href="tareas.php">Ver todas</a>
        </header>

        <?php if (empty($tareas)): ?>
            <div class="estado-vacio">
                <p class="estado-vacio-titulo">Todavía no hay tareas para mostrar.</p>
                <p class="estado-vacio-texto">Cuando se registren tareas, aparecerán aquí con su responsable y estado.</p>
            </div>
        <?php else: ?>
            <div class="tabla-envoltura">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarea</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Actualizado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tareas as $tarea): ?>
                            <tr>
                                <td class="celda-id">#<?= str_pad((string) $tarea['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="celda-tarea">
                                    <span class="titulo-tarea"><?= e($tarea['titulo']) ?></span>
                                    <span class="nota"><?= e($tarea['proyecto']) ?></span>
                                </td>
                                <td data-etiqueta="Responsable"><?= e($tarea['responsable']) ?></td>
                                <td data-etiqueta="Estado">
                                    <span class="pildora <?= e(clase_estado($tarea['estado'])) ?>"><?= e($tarea['estado']) ?></span>
                                </td>
                                <td class="celda-fecha"><?= e(fecha_corta($tarea['actualizado_en'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <aside class="panel panel-lateral">
        <div class="panel-cuerpo">
            <p class="sobretitulo">Distribución</p>
            <h2>Estado de las tareas</h2>

            <div class="lista-progreso">
                <?php foreach ($desglose as $estado => $cantidad): ?>
                    <?php $ancho = $totalTareas > 0 ? (int) round($cantidad * 100 / $totalTareas) : 0; ?>
                    <div class="fila-progreso">
                        <div class="fila-progreso-texto">
                            <span><?= e($estado) ?></span>
                            <strong><?= (int) $cantidad ?></strong>
                        </div>
                        <div class="barra">
                            <span class="<?= e($clasesBarra[$estado]) ?>" style="width: <?= $ancho ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="nota-lateral">
                <p class="nota-lateral-titulo">Siguiente paso</p>
                <p><?= e($textoNota) ?></p>
                <a class="enlace-flecha" href="<?= e($enlaceNota) ?>">Continuar</a>
            </div>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
