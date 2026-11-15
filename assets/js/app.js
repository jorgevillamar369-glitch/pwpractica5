/* ============================================================
   Pwa5 · Interacciones sencillas (JavaScript sin dependencias)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    /* Menú lateral en pantallas pequeñas */
    var menuBoton = document.getElementById('menuBoton');
    var lateral = document.getElementById('lateral');
    var velo = document.getElementById('velo');

    function cerrarMenu() {
        if (!lateral || !velo) {
            return;
        }
        lateral.classList.remove('abierto');
        velo.classList.remove('visible');
        document.body.classList.remove('sin-scroll');
    }

    if (menuBoton && lateral && velo) {
        menuBoton.addEventListener('click', function () {
            lateral.classList.toggle('abierto');
            velo.classList.toggle('visible');
            document.body.classList.toggle('sin-scroll');
        });

        velo.addEventListener('click', cerrarMenu);

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                cerrarMenu();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) {
                cerrarMenu();
            }
        });
    }

    /* Modal de confirmación para eliminar */
    var modalElemento = document.getElementById('modalEliminar');
    var formularioEliminar = document.getElementById('formEliminar');
    var idEliminar = document.getElementById('idEliminar');
    var nombreEliminar = document.getElementById('nombreEliminar');

    if (modalElemento && formularioEliminar && idEliminar && nombreEliminar && window.bootstrap) {
        var modal = new bootstrap.Modal(modalElemento);

        document.querySelectorAll('.js-eliminar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                formularioEliminar.action = boton.dataset.url;
                idEliminar.value = boton.dataset.id;
                nombreEliminar.textContent = boton.dataset.nombre;
                modal.show();
            });
        });
    }

    /* Mostrar u ocultar contraseñas */
    document.querySelectorAll('.ver-clave').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var entrada = document.getElementById(boton.dataset.objetivo);
            if (!entrada) {
                return;
            }

            var estabaOculta = entrada.type === 'password';
            entrada.type = estabaOculta ? 'text' : 'password';
            boton.classList.toggle('activo', estabaOculta);
            boton.setAttribute('aria-label', estabaOculta ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    /* Contador de caracteres de las descripciones */
    document.querySelectorAll('[data-contador]').forEach(function (campo) {
        var salida = document.getElementById(campo.dataset.contador);
        if (!salida) {
            return;
        }

        var actualizar = function () {
            salida.textContent = campo.value.length + ' / ' + campo.maxLength;
        };

        campo.addEventListener('input', actualizar);
        actualizar();
    });

    /* El miembro cambia el estado desde la propia tabla */
    document.querySelectorAll('.js-cambiar-estado').forEach(function (selector) {
        selector.addEventListener('change', function () {
            var formulario = selector.closest('form');
            if (!formulario) {
                return;
            }

            selector.disabled = true;
            if (typeof formulario.requestSubmit === 'function') {
                formulario.requestSubmit();
            } else {
                formulario.submit();
            }
        });
    });

    /* Marca que JavaScript está activo: oculta el botón de respaldo */
    document.documentElement.classList.add('js');
});
