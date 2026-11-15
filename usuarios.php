<?php
/**
 * Listado de usuarios. Solo para el Administrador.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol([1]);

$usuarios = $pdo->query(
    'SELECT u.id, u.nombre, u.email, u.rol_id, r.rol_nombre,
            (SELECT COUNT(*) FROM tareas t WHERE t.usuario_id = u.id) AS tareas,
            (SELECT COUNT(*) FROM proyectos p WHERE p.gerente_id = u.id) AS proyectos
     FROM usuarios u
     INNER JOIN roles r ON r.rol_id = u.rol_id
     ORDER BY u.nombre'
)->fetchAll();

$u = usuario_actual();

$titulo    = 'Usuarios';
$seccion   = 'usuarios';
$subtitulo = 'Administración de cuentas';
$acciones  = '<a class="btn btn-primario" href="usuario_form.php">Nuevo usuario</a>';

require __DIR__ . '/includes/header.php';
?>

<section class="panel">
    <header class="panel-cabecera">
        <div>
            <p class="sobretitulo">Directorio</p>
            <h2><?= count($usuarios) ?> <?= count($usuarios) === 1 ? 'cuenta registrada' : 'cuentas registradas' ?></h2>
        </div>
        <p class="panel-nota">Los roles definen qué puede hacer cada persona dentro del sistema.</p>
    </header>

    <?php if (empty($usuarios)): ?>
        <div class="estado-vacio">
            <p class="estado-vacio-titulo">No hay usuarios registrados.</p>
            <p class="estado-vacio-texto">Crea la primera cuenta para empezar a asignar roles y tareas.</p>
            <a class="btn btn-primario" href="usuario_form.php">Crear usuario</a>
        </div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Tareas</th>
                        <th>Proyectos</th>
                        <th class="columna-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td class="celda-tarea">
                                <div class="celda-usuario">
                                    <span class="avatar avatar-claro"><?= e(iniciales($usuario['nombre'])) ?></span>
                                    <div>
                                        <span class="titulo-tarea"><?= e($usuario['nombre']) ?></span>
                                        <span class="nota"><?= e($usuario['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td data-etiqueta="Rol">
                                <span class="etiqueta-rol rol-<?= (int) $usuario['rol_id'] ?>">
                                    <?= e($usuario['rol_nombre']) ?>
                                </span>
                            </td>
                            <td class="celda-numero" data-etiqueta="Tareas"><?= (int) $usuario['tareas'] ?></td>
                            <td class="celda-numero" data-etiqueta="Proyectos"><?= (int) $usuario['proyectos'] ?></td>
                            <td data-etiqueta="Acciones">
                                <div class="acciones-fila">
                                    <a class="accion" href="usuario_form.php?id=<?= (int) $usuario['id'] ?>">Editar</a>
                                    <?php if ((int) $usuario['id'] === (int) $u['id']): ?>
                                        <span class="accion-inactiva">Tu cuenta</span>
                                    <?php else: ?>
                                        <button type="button" class="accion accion-peligro js-eliminar"
                                                data-url="usuario_eliminar.php"
                                                data-id="<?= (int) $usuario['id'] ?>"
                                                data-nombre="<?= e($usuario['nombre']) ?>">
                                            Eliminar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
