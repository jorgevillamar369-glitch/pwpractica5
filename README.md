# Pwa5 · Sistema de Gestión de Tareas por Equipos

Práctica 5 de Programación Web Avanzada. Aplicación web en PHP y MySQL para organizar proyectos y tareas con tres roles: Administrador, Gerente de proyecto y Miembro del equipo.

Sitio publicado: `https://pwapractica5.infinityfreeapp.com`

## Tecnologías

- PHP 8 (PDO con consultas preparadas)
- MySQL 8 / MariaDB
- HTML5, CSS propio y Bootstrap 5
- JavaScript sin librerías adicionales
- Sesiones de PHP y contraseñas cifradas con `password_hash`

## Roles y permisos

| Rol | Puede hacer |
| --- | --- |
| Administrador | Crear, editar y eliminar usuarios; asignar roles; crear proyectos y designar gerentes; consultar todos los proyectos y tareas. |
| Gerente de proyecto | Crear, editar y eliminar tareas de sus proyectos; asignarlas a miembros; consultar su estado. |
| Miembro del equipo | Ver las tareas que tiene asignadas y actualizar su estado. |

## Cuentas de demostración

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | `admin@ejemplo.com` | `Admin123!` |
| Gerente | `gerente@ejemplo.com` | `Gerente123!` |
| Miembro | `miembro@ejemplo.com` | `Miembro123!` |

También existen `carlos@ejemplo.com` (miembro) y `rosa@ejemplo.com` (gerente) con las mismas contraseñas de su rol.

## Grupo 04

| Integrante | Rol en el sistema | Correo |
| --- | --- | --- |
| Jorge Villamar Sánchez | Administrador | `jorge.villamar@ejemplo.com` |
| Norka Mideros Quiñonez | Gerente de proyecto | `norka.mideros@ejemplo.com` |
| Bruce Balarezo Chacón | Miembro del equipo | `bruce.balarezo@ejemplo.com` |
| Mauro Chinlle Carrillo | Miembro del equipo | `mauro.chinlle@ejemplo.com` |
| Liseth Criollo | Miembro del equipo | `liseth.criollo@ejemplo.com` |

Las cinco cuentas usan la contraseña inicial `Grupo04-2026!`. El administrador puede cambiarla desde el módulo de usuarios.

Repositorio del grupo: `https://github.com/jorgevillamar369-glitch/pwpractica5`.

En un sitio publicado, cambia estas contraseñas desde el módulo de usuarios.

## Registro de cuentas

La página `registro.php` permite que cualquier visitante cree su propia cuenta.

- Pide nombre completo, correo, contraseña y confirmación.
- Valida el formato del correo, que no esté repetido y que la contraseña tenga al menos 8 caracteres.
- Guarda la contraseña con `password_hash`, nunca en texto plano.
- Asigna el rol **Miembro del equipo** (3) a toda cuenta nueva.
- Advierte que es un sitio de práctica: no se deben usar correos ni contraseñas reales de Google u otros servicios, y enlaza con las cuentas de demostración.
- Al terminar, redirige al login con el aviso «Cuenta creada. Ya puedes iniciar sesión con tu correo.».

El administrador puede cambiar el rol de esas cuentas desde `usuarios.php`. Desde el login, el enlace «¿No tienes cuenta? Crear una cuenta» lleva al registro.

## Instalación local con Laragon

1. Copia la carpeta del proyecto en `C:\laragon\www\pwapractica5`.
2. Inicia MySQL desde Laragon. Esta instalación usa el puerto **3307** porque el 3306 está ocupado por otro servidor MySQL; el puerto se configura en `includes/config.php`.
3. Crea la base de datos `pwapractica5` con cotejamiento `utf8mb4_unicode_ci`.
4. Importa `sql/pwapractica5.sql` con phpMyAdmin o desde la consola:

   ```sql
   mysql -u root -p -P 3307 -D pwapractica5
   source C:/laragon/www/pwapractica5/sql/pwapractica5.sql
   ```

5. Copia `includes/config.example.php` como `includes/config.php` y ajusta los datos:

   ```php
   const DB_HOST = '127.0.0.1';
   const DB_PORT = '3307';
   const DB_NOMBRE = 'pwapractica5';
   const DB_USUARIO = 'pwapractica5';
   const DB_CLAVE = 'tu_clave';
   ```

6. Abre `http://pwapractica5.test` o `http://localhost/pwapractica5`.

Si usas XAMPP, el puerto normal es `3306` y la carpeta va en `C:\xampp\htdocs\pwapractica5`.

## Publicación en un hospedaje

1. Crea la base de datos y el usuario desde el panel del hospedaje.
2. Importa `sql/pwapractica5.sql` desde phpMyAdmin.
3. Sube el proyecto por FTP o desde el administrador de archivos.
4. Copia `includes/config.example.php` como `includes/config.php` con los datos del hospedaje. Normalmente el puerto es `3306`.
5. Deja `APP_DEBUG` en `false`.
6. Cambia las contraseñas de las cuentas de demostración.

## Estructura

```
pwapractica5/
├── index.php                 Redirige al panel o al login
├── login.php                 Inicio de sesión
├── registro.php              Registro público de cuentas
├── logout.php                Cierre de sesión
├── dashboard.php             Panel según el rol
├── usuarios.php              Listado de usuarios (administrador)
├── usuario_form.php          Alta y edición de usuarios
├── usuario_eliminar.php      Baja de usuarios
├── proyectos.php             Listado de proyectos
├── proyecto_form.php         Alta y edición de proyectos
├── proyecto_eliminar.php     Baja de proyectos
├── tareas.php                Listado y filtros de tareas
├── tarea_form.php            Alta y edición de tareas
├── tarea_estado.php          Cambio de estado
├── tarea_eliminar.php        Baja de tareas
├── includes/
│   ├── config.php            Conexión local (no se publica)
│   ├── config.example.php    Plantilla de configuración
│   ├── auth.php              Sesión y permisos
│   ├── funciones.php         Utilidades y validaciones
│   ├── header.php            Menú y cabecera
│   └── footer.php            Pie, modal y scripts
├── assets/
│   ├── css/estilos.css
│   ├── js/app.js
│   └── img/
├── sql/pwapractica5.sql
└── README.md
```

## Cambios respecto al esquema del enunciado

El documento entrega las tablas `roles`, `usuarios` y `tareas`, pero los roles hablan de «su propio proyecto». Para que esa idea funcione se añadieron dos elementos mínimos:

- Tabla `proyectos` (`id`, `nombre`, `descripcion`, `gerente_id`).
- Columna `tareas.proyecto_id` con su llave foránea.

El resto se conserva: los tres roles, la columna `contraseña`, el `CHECK` del estado y la asignación de cada tarea a un usuario.

## Seguridad

- Consultas preparadas en todas las operaciones con la base de datos.
- Contraseñas cifradas con `password_hash` y verificadas con `password_verify`.
- Token CSRF en cada formulario que modifica datos.
- Escape de todo el texto que se imprime en HTML.
- Validación de permisos en cada página y en cada acción, no solo en el menú.
- `includes/config.php` está excluido del repositorio con `.gitignore`.

## Créditos

Fotografía de la pantalla de acceso: Pexels.
