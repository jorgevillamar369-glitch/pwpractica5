<?php
/**
 * Crear o editar una tarea. Solo para el Gerente de proyecto,
 * y únicamente dentro de sus propios proyectos.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([2]);

$u   = usuario_actual();
$uid = (int) $u['id'];
$id  = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$consulta = $pdo->prepare('SELECT id, nombre FROM proyectos WHERE gerente_id = ? ORDER BY nombre');
$consulta->execute([$uid]);
$proyectos = $consulta->fetchAll();

if (empty($proyectos)) {
    flash('error', 'Necesitas tener al menos un proyecto asignado para crear tareas.');
    redirigir('tareas.php');
}

$consulta = $pdo->query('SELECT id, nombre FROM usuarios WHERE rol_id = 3 ORDER BY nombre');
$miembros = $consulta->fetchAll();

$datos = [
    'titulo'      => '',
    'descripcion' => '',
    'estado'      => 'Pendiente',
    'usuario_id'  => 0,
    'proyecto_id' => (int) ($_GET['proyecto'] ?? 0),
];
$errores  = [];
$editando = false;

if ($id > 0) {
    $consulta = $pdo->prepare(
        'SELECT t.id, t.titulo, t.descripcion, t.estado, t.usuario_id, t.proyecto_id
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

    $editando = true;
    $datos = [
        'titulo'      => $tarea['titulo'],
        'descripcion' => (string) $tarea['descripcion'],
        'estado'      => $tarea['estado'],
        'usuario_id'  => (int) $tarea['usuario_id'],
        'proyecto_id' => (int) $tarea['proyecto_id'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $datos = [
        'titulo'      => trim((string) ($_POST['titulo'] ?? '')),
        'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        'estado'      => (string) ($_POST['estado'] ?? ''),
        'usuario_id'  => (int) ($_POST['usuario_id'] ?? 0),
        'proyecto_id' => (int) ($_POST['proyecto_id'] ?? 0),
    ];

    if (mb_strlen($datos['titulo']) < 3 || mb_strlen($datos['titulo']) > 50) {
        $errores['titulo'] = 'El título debe tener entre 3 y 50 caracteres.';
    }

    if (mb_strlen($datos['descripcion']) > 300) {
        $errores['descripcion'] = 'La descripción no puede pasar de 300 caracteres.';
    }

    if (!in_array($datos['estado'], estados_validos(), true)) {
        $errores['estado'] = 'Selecciona un estado válido.';
    }

    $idsProyectos = array_map(static fn($proyecto) => (int) $proyecto['id'], $proyectos);
    if (!in_array($datos['proyecto_id'], $idsProyectos, true)) {
        $errores['proyecto_id'] = 'Selecciona uno de tus proyectos.';
    }

    $idsMiembros = array_map(static fn($miembro) => (int) $miembro['id'], $miembros);
    if (!in_array($datos['usuario_id'], $idsMiembros, true)) {
        $errores['usuario_id'] = 'Selecciona un miembro del equipo.';
    }

    if (empty($errores)) {
        if ($editando) {
            $consulta = $pdo->prepare(
                'UPDATE tareas SET titulo = ?, descripcion = ?, estado = ?, usuario_id = ?, proyecto_id = ?
                 WHERE id = ?'
            );
            $consulta->execute([
                $datos['titulo'],
                $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                $datos['estado'],
                $datos['usuario_id'],
                $datos['proyecto_id'],
                $id,
            ]);
            flash('exito', 'Tarea actualizada correctamente.');
        } else {
            $consulta = $pdo->prepare(
                'INSERT INTO tareas (titulo, descripcion, estado, usuario_id, proyecto_id)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $consulta->execute([
                $datos['titulo'],
                $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                $datos['estado'],
                $datos['usuario_id'],
                $datos['proyecto_id'],
            ]);
            flash('exito', 'Tarea creada y asignada al miembro del equipo.');
        }

        redirigir('tareas.php');
    }
}

$titulo    = $editando ? 'Editar tarea' : 'Nueva tarea';
$seccion   = 'tareas';
$subtitulo = 'Asignación de trabajo';
$acciones  = '<a class="btn btn-contorno" href="tareas.php">Volver al listado</a>';

require __DIR__ . '/includes/header.php';
?>

<?php if (empty($miembros)): ?>
    <div class="aviso aviso-aviso" role="status">
        <span>No hay usuarios con rol «Miembro del equipo». El administrador debe crear al menos uno para asignar tareas.</span>
    </div>
<?php endif; ?>

<form method="post" class="formulario" action="tarea_form.php<?= $editando ? '?id=' . $id : '' ?>" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <?php if (!empty($errores)): ?>
        <div class="aviso aviso-error" role="alert">
            <span>Revisa los campos marcados antes de guardar.</span>
        </div>
    <?php endif; ?>

    <section class="panel">
        <header class="panel-cabecera">
            <div>
                <p class="sobretitulo">Detalle</p>
                <h2>Qué hay que hacer</h2>
            </div>
        </header>

        <div class="panel-cuerpo">
            <div class="campo">
                <label for="titulo">Título de la tarea</label>
                <input type="text" class="form-control <?= isset($errores['titulo']) ? 'is-invalid' : '' ?>"
                       id="titulo" name="titulo" value="<?= e($datos['titulo']) ?>"
                       maxlength="50" required>
                <?php if (isset($errores['titulo'])): ?>
                    <p class="error-campo"><?= e($errores['titulo']) ?></p>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                          maxlength="300" data-contador="contadorDescripcion"><?= e($datos['descripcion']) ?></textarea>
                <div class="campo-pie">
                    <p class="ayuda-campo">Concreto y verificable: qué hay que hacer y cómo se sabe que está listo.</p>
                    <span class="contador" id="contadorDescripcion">0 / 300</span>
                </div>
                <?php if (isset($errores['descripcion'])): ?>
                    <p class="error-campo"><?= e($errores['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-cabecera">
            <div>
                <p class="sobretitulo">Asignación</p>
                <h2>Quién la hace y en qué proyecto</h2>
            </div>
        </header>

        <div class="panel-cuerpo">
            <div class="rejilla-formulario">
                <div class="campo">
                    <label for="proyecto_id">Proyecto</label>
                    <select class="form-select <?= isset($errores['proyecto_id']) ? 'is-invalid' : '' ?>"
                            id="proyecto_id" name="proyecto_id" required>
                        <option value="">Selecciona un proyecto</option>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <option value="<?= (int) $proyecto['id'] ?>" <?= (int) $proyecto['id'] === (int) $datos['proyecto_id'] ? 'selected' : '' ?>>
                                <?= e($proyecto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['proyecto_id'])): ?>
                        <p class="error-campo"><?= e($errores['proyecto_id']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="usuario_id">Responsable</label>
                    <select class="form-select <?= isset($errores['usuario_id']) ? 'is-invalid' : '' ?>"
                            id="usuario_id" name="usuario_id" required>
                        <option value="">Selecciona un miembro</option>
                        <?php foreach ($miembros as $miembro): ?>
                            <option value="<?= (int) $miembro['id'] ?>" <?= (int) $miembro['id'] === (int) $datos['usuario_id'] ? 'selected' : '' ?>>
                                <?= e($miembro['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['usuario_id'])): ?>
                        <p class="error-campo"><?= e($errores['usuario_id']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="campo">
                <label for="estado">Estado inicial</label>
                <select class="form-select <?= isset($errores['estado']) ? 'is-invalid' : '' ?>"
                        id="estado" name="estado" required>
                    <?php foreach (estados_validos() as $opcion): ?>
                        <option value="<?= e($opcion) ?>" <?= $datos['estado'] === $opcion ? 'selected' : '' ?>>
                            <?= e($opcion) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="ayuda-campo">El miembro podrá actualizarlo cuando avance.</p>
                <?php if (isset($errores['estado'])): ?>
                    <p class="error-campo"><?= e($errores['estado']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="formulario-acciones">
        <button type="submit" class="btn btn-primario" <?= empty($miembros) ? 'disabled' : '' ?>>
            <?= $editando ? 'Guardar cambios' : 'Crear tarea' ?>
        </button>
        <a class="btn btn-contorno" href="tareas.php">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
