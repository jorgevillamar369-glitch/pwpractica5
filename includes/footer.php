        </main>

        <footer class="pie">
            <p>Práctica 5 · Sistema de Gestión de Tareas por Equipos</p>
            <p>Fotografía: Pexels</p>
        </footer>
    </div>
</div>

<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="tituloModalEliminar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-confirmacion">
            <div class="modal-header">
                <h2 class="modal-title" id="tituloModalEliminar">Confirmar eliminación</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-contorno" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" id="formEliminar">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" id="idEliminar" value="">
                    <button type="submit" class="btn btn-peligro">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
