<?php
/**
 * Crear o editar un proyecto. Solo para el Administrador.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1]);

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$consulta = $pdo->query(
    'SELECT id, nombre FROM usuarios WHERE rol_id = 2 ORDER BY nombre'
);
$gerentes = $consulta->fetchAll();

$datos = ['nombre' => '', 'descripcion' => '', 'gerente_id' => 0];
$errores = [];
$editando = false;

if ($id > 0) {
    $consulta = $pdo->prepare('SELECT id, nombre, descripcion, gerente_id FROM proyectos WHERE id = ?');
    $consulta->execute([$id]);
    $proyecto = $consulta->fetch();

    if (!$proyecto) {
        flash('error', 'El proyecto que buscas no existe.');
        redirigir('proyectos.php');
    }

    $editando = true;
    $datos = [
        'nombre'      => $proyecto['nombre'],
        'descripcion' => (string) $proyecto['descripcion'],
        'gerente_id'  => (int) $proyecto['gerente_id'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $datos = [
        'nombre'      => trim((string) ($_POST['nombre'] ?? '')),
        'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        'gerente_id'  => (int) ($_POST['gerente_id'] ?? 0),
    ];

    if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 100) {
        $errores['nombre'] = 'El nombre debe tener entre 3 y 100 caracteres.';
    }

    if (mb_strlen($datos['descripcion']) > 300) {
        $errores['descripcion'] = 'La descripción no puede pasar de 300 caracteres.';
    }

    $idsGerentes = array_map(static fn($gerente) => (int) $gerente['id'], $gerentes);
    if (!in_array($datos['gerente_id'], $idsGerentes, true)) {
        $errores['gerente_id'] = 'Selecciona un gerente de proyecto válido.';
    }

    if (empty($errores)) {
        if ($editando) {
            $consulta = $pdo->prepare(
                'UPDATE proyectos SET nombre = ?, descripcion = ?, gerente_id = ? WHERE id = ?'
            );
            $consulta->execute([
                $datos['nombre'],
                $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                $datos['gerente_id'],
                $id,
            ]);
            flash('exito', 'Proyecto actualizado correctamente.');
        } else {
            $consulta = $pdo->prepare(
                'INSERT INTO proyectos (nombre, descripcion, gerente_id) VALUES (?, ?, ?)'
            );
            $consulta->execute([
                $datos['nombre'],
                $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                $datos['gerente_id'],
            ]);
            flash('exito', 'Proyecto creado. El gerente ya puede añadir tareas.');
        }

        redirigir('proyectos.php');
    }
}

$titulo    = $editando ? 'Editar proyecto' : 'Nuevo proyecto';
$seccion   = 'proyectos';
$subtitulo = 'Organización del trabajo';
$acciones  = '<a class="btn btn-contorno" href="proyectos.php">Volver al listado</a>';

require __DIR__ . '/includes/header.php';
?>

<?php if (empty($gerentes)): ?>
    <div class="aviso aviso-aviso" role="status">
        <span>No hay usuarios con rol «Gerente de proyecto». Crea uno antes de registrar un proyecto.</span>
    </div>
<?php endif; ?>

<form method="post" class="formulario" action="proyecto_form.php<?= $editando ? '?id=' . $id : '' ?>" novalidate>
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
                <p class="sobretitulo">Ficha del proyecto</p>
                <h2><?= $editando ? 'Datos actuales' : 'Datos iniciales' ?></h2>
            </div>
        </header>

        <div class="panel-cuerpo">
            <div class="campo">
                <label for="nombre">Nombre del proyecto</label>
                <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                       id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>"
                       maxlength="100" required>
                <?php if (isset($errores['nombre'])): ?>
                    <p class="error-campo"><?= e($errores['nombre']) ?></p>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                          maxlength="300" data-contador="contadorDescripcion"><?= e($datos['descripcion']) ?></textarea>
                <div class="campo-pie">
                    <p class="ayuda-campo">Una frase breve sobre qué resuelve el proyecto.</p>
                    <span class="contador" id="contadorDescripcion">0 / 300</span>
                </div>
                <?php if (isset($errores['descripcion'])): ?>
                    <p class="error-campo"><?= e($errores['descripcion']) ?></p>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="gerente_id">Gerente responsable</label>
                <select class="form-select <?= isset($errores['gerente_id']) ? 'is-invalid' : '' ?>"
                        id="gerente_id" name="gerente_id" required>
                    <option value="">Selecciona una persona</option>
                    <?php foreach ($gerentes as $gerente): ?>
                        <option value="<?= (int) $gerente['id'] ?>" <?= (int) $gerente['id'] === (int) $datos['gerente_id'] ? 'selected' : '' ?>>
                            <?= e($gerente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['gerente_id'])): ?>
                    <p class="error-campo"><?= e($errores['gerente_id']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="formulario-acciones">
        <button type="submit" class="btn btn-primario" <?= empty($gerentes) ? 'disabled' : '' ?>>
            <?= $editando ? 'Guardar cambios' : 'Crear proyecto' ?>
        </button>
        <a class="btn btn-contorno" href="proyectos.php">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
