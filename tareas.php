<?php
/**
 * Listado de tareas con filtros.
 * El Administrador ve todas, el Gerente las de sus proyectos
 * y el Miembro solo las que tiene asignadas.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_login();

$u   = usuario_actual();
$rol = (int) $u['rol_id'];
$uid = (int) $u['id'];

$busqueda   = trim((string) ($_GET['q'] ?? ''));
$estado     = (string) ($_GET['estado'] ?? '');
$proyectoId = (int) ($_GET['proyecto'] ?? 0);

if (!in_array($estado, estados_validos(), true)) {
    $estado = '';
}

$condiciones = [];
$parametros  = [];

if ($rol === 2) {
    $condiciones[] = 'p.gerente_id = ?';
    $parametros[]  = $uid;
} elseif ($rol === 3) {
    $condiciones[] = 't.usuario_id = ?';
    $parametros[]  = $uid;
}

if ($estado !== '') {
    $condiciones[] = 't.estado = ?';
    $parametros[]  = $estado;
}

if ($proyectoId > 0 && $rol !== 3) {
    $condiciones[] = 't.proyecto_id = ?';
    $parametros[]  = $proyectoId;
}

if ($busqueda !== '') {
    $condiciones[] = '(t.titulo LIKE ? OR t.descripcion LIKE ? OR us.nombre LIKE ?)';
    $like = '%' . $busqueda . '%';
    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
}

$sql = 'SELECT t.id, t.titulo, t.descripcion, t.estado, t.actualizado_en, t.proyecto_id,
               p.nombre AS proyecto, us.nombre AS responsable
        FROM tareas t
        INNER JOIN proyectos p ON p.id = t.proyecto_id
        INNER JOIN usuarios us ON us.id = t.usuario_id';

if (!empty($condiciones)) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}

$sql .= " ORDER BY CASE t.estado
            WHEN 'Pendiente' THEN 1
            WHEN 'En proceso' THEN 2
            ELSE 3
         END, t.actualizado_en DESC";

$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$tareas = $consulta->fetchAll();

$conteo = ['Pendiente' => 0, 'En proceso' => 0, 'Completado' => 0];
foreach ($tareas as $tarea) {
    $conteo[$tarea['estado']]++;
}

if ($rol === 1) {
    $proyectosFiltro = $pdo->query('SELECT id, nombre FROM proyectos ORDER BY nombre')->fetchAll();
} elseif ($rol === 2) {
    $consulta = $pdo->prepare('SELECT id, nombre FROM proyectos WHERE gerente_id = ? ORDER BY nombre');
    $consulta->execute([$uid]);
    $proyectosFiltro = $consulta->fetchAll();
} else {
    $proyectosFiltro = [];
}

$puedeCrear = $rol === 2 && !empty($proyectosFiltro);
$hayFiltros = $busqueda !== '' || $estado !== '' || $proyectoId > 0;

$textoPendientes  = $conteo['Pendiente'] . ' ' . ($conteo['Pendiente'] === 1 ? 'pendiente' : 'pendientes');
$textoCompletadas = $conteo['Completado'] . ' ' . ($conteo['Completado'] === 1 ? 'completada' : 'completadas');

$titulo    = 'Tareas';
$seccion   = 'tareas';
$subtitulo = $rol === 1 ? 'Vista general' : ($rol === 2 ? 'Tareas de tus proyectos' : 'Tus tareas asignadas');
$acciones  = $puedeCrear ? '<a class="btn btn-primario" href="tarea_form.php">Nueva tarea</a>' : '';

require __DIR__ . '/includes/header.php';
?>

<section class="panel panel-intro">
    <div class="panel-cuerpo panel-cuerpo-fila">
        <div>
            <p class="sobretitulo">Resumen</p>
            <h2><?= count($tareas) ?> <?= count($tareas) === 1 ? 'tarea visible' : 'tareas visibles' ?></h2>
            <p class="panel-nota">
                <?= e($textoPendientes) ?> ·
                <?= (int) $conteo['En proceso'] ?> en proceso ·
                <?= e($textoCompletadas) ?>
            </p>
        </div>
        <?php if ($rol === 3): ?>
            <p class="panel-nota panel-nota-derecha">Solo puedes cambiar el estado de las tareas que tienes asignadas.</p>
        <?php endif; ?>
    </div>
</section>

<form class="filtros" method="get" action="tareas.php">
    <div class="filtro-campo filtro-buscar">
        <label class="visualmente-oculto" for="q">Buscar tarea</label>
        <input type="search" class="form-control" id="q" name="q" value="<?= e($busqueda) ?>"
               placeholder="Buscar por tarea o responsable">
    </div>

    <div class="filtro-campo">
        <label class="visualmente-oculto" for="estado">Estado</label>
        <select class="form-select" id="estado" name="estado">
            <option value="">Todos los estados</option>
            <?php foreach (estados_validos() as $opcion): ?>
                <option value="<?= e($opcion) ?>" <?= $estado === $opcion ? 'selected' : '' ?>><?= e($opcion) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($rol !== 3 && !empty($proyectosFiltro)): ?>
        <div class="filtro-campo">
            <label class="visualmente-oculto" for="proyecto">Proyecto</label>
            <select class="form-select" id="proyecto" name="proyecto">
                <option value="">Todos los proyectos</option>
                <?php foreach ($proyectosFiltro as $proyecto): ?>
                    <option value="<?= (int) $proyecto['id'] ?>" <?= $proyectoId === (int) $proyecto['id'] ? 'selected' : '' ?>>
                        <?= e($proyecto['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-contorno">Filtrar</button>
    <?php if ($hayFiltros): ?>
        <a class="accion" href="tareas.php">Limpiar</a>
    <?php endif; ?>
</form>

<section class="panel">
    <?php if (empty($tareas)): ?>
        <div class="estado-vacio">
            <?php if ($hayFiltros): ?>
                <p class="estado-vacio-titulo">No hay tareas que coincidan con el filtro.</p>
                <p class="estado-vacio-texto">Prueba con otro estado, otro proyecto o borra la búsqueda.</p>
                <a class="btn btn-contorno" href="tareas.php">Limpiar filtros</a>
            <?php elseif ($rol === 2 && empty($proyectosFiltro)): ?>
                <p class="estado-vacio-titulo">Todavía no tienes proyectos asignados.</p>
                <p class="estado-vacio-texto">Pide al administrador que te asigne un proyecto para poder crear tareas.</p>
            <?php elseif ($rol === 3): ?>
                <p class="estado-vacio-titulo">No tienes tareas asignadas.</p>
                <p class="estado-vacio-texto">Cuando el gerente te asigne una, aparecerá en esta lista.</p>
            <?php else: ?>
                <p class="estado-vacio-titulo">Todavía no hay tareas registradas.</p>
                <p class="estado-vacio-texto">Crea la primera tarea y asígnala a un miembro del equipo.</p>
                <?php if ($puedeCrear): ?>
                    <a class="btn btn-primario" href="tarea_form.php">Crear tarea</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarea</th>
                        <th>Proyecto</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Actualizado</th>
                        <?php if ($rol !== 1): ?>
                            <th class="columna-acciones">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $tarea): ?>
                        <tr>
                            <td class="celda-id">#<?= str_pad((string) $tarea['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="celda-tarea">
                                <span class="titulo-tarea"><?= e($tarea['titulo']) ?></span>
                                <?php if (!empty($tarea['descripcion'])): ?>
                                    <span class="nota"><?= e($tarea['descripcion']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-etiqueta="Proyecto"><?= e($tarea['proyecto']) ?></td>
                            <td data-etiqueta="Responsable"><?= e($tarea['responsable']) ?></td>
                            <td data-etiqueta="Estado">
                                <span class="pildora <?= e(clase_estado($tarea['estado'])) ?>"><?= e($tarea['estado']) ?></span>
                            </td>
                            <td class="celda-fecha"><?= e(fecha_corta($tarea['actualizado_en'])) ?></td>

                            <?php if ($rol === 2): ?>
                                <td data-etiqueta="Acciones">
                                    <div class="acciones-fila">
                                        <a class="accion" href="tarea_form.php?id=<?= (int) $tarea['id'] ?>">Editar</a>
                                        <button type="button" class="accion accion-peligro js-eliminar"
                                                data-url="tarea_eliminar.php"
                                                data-id="<?= (int) $tarea['id'] ?>"
                                                data-nombre="<?= e($tarea['titulo']) ?>">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            <?php elseif ($rol === 3): ?>
                                <td data-etiqueta="Acciones">
                                    <form method="post" action="tarea_estado.php" class="form-estado">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $tarea['id'] ?>">
                                        <select name="estado" class="form-select form-select-sm js-cambiar-estado"
                                                aria-label="Estado de <?= e($tarea['titulo']) ?>">
                                            <?php foreach (estados_validos() as $opcion): ?>
                                                <option value="<?= e($opcion) ?>" <?= $tarea['estado'] === $opcion ? 'selected' : '' ?>>
                                                    <?= e($opcion) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-mini btn-contorno btn-estado">Guardar</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
