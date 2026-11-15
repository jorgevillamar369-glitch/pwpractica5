-- ---------------------------------------------------------------
-- Práctica 5 · Sistema de Gestión de Tareas por Equipos
-- Base de datos para MySQL 8.x / MariaDB 10.x
-- ---------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tareas;
DROP TABLE IF EXISTS proyectos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- Roles del sistema
-- ---------------------------------------------------------------
CREATE TABLE roles (
    rol_id INT AUTO_INCREMENT PRIMARY KEY,
    rol_nombre VARCHAR(30) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO roles (rol_id, rol_nombre) VALUES
    (1, 'Administrador'),
    (2, 'Gerente de proyecto'),
    (3, 'Miembro del equipo');

-- ---------------------------------------------------------------
-- Usuarios
-- La columna conserva el nombre del enunciado: contraseña
-- ---------------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    `contraseña` VARCHAR(100) NOT NULL,
    rol_id INT NOT NULL,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (rol_id) REFERENCES roles (rol_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ---------------------------------------------------------------
-- Proyectos (tabla añadida para dar sentido a los roles)
-- Cada proyecto tiene un gerente responsable.
-- ---------------------------------------------------------------
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(300),
    gerente_id INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_proyectos_usuarios FOREIGN KEY (gerente_id) REFERENCES usuarios (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ---------------------------------------------------------------
-- Tareas
-- Se añade proyecto_id para relacionar cada tarea con su proyecto.
-- ---------------------------------------------------------------
CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(300),
    estado VARCHAR(10) NOT NULL DEFAULT 'Pendiente',
    usuario_id INT NOT NULL,
    proyecto_id INT NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_tareas_estado CHECK (estado IN ('Pendiente', 'En proceso', 'Completado')),
    CONSTRAINT fk_tareas_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    CONSTRAINT fk_tareas_proyectos FOREIGN KEY (proyecto_id) REFERENCES proyectos (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ---------------------------------------------------------------
-- Datos de demostración
-- Contraseñas: Admin123! / Gerente123! / Miembro123!
-- ---------------------------------------------------------------
INSERT INTO usuarios (id, nombre, email, `contraseña`, rol_id) VALUES
    (1, 'Ana Torres', 'admin@ejemplo.com', '$2y$10$dv0wemZPuGQNpu8CBVn58.8I4nmF4HoV1jLyKrV5AOHoCeDEK4m/C', 1),
    (2, 'Luis Mendoza', 'gerente@ejemplo.com', '$2y$10$GFWpiU0WZ6fwlPLRxLp0lecglKneMqgLmIz2rFI/OxVMeQUf2klPa', 2),
    (3, 'María Paredes', 'miembro@ejemplo.com', '$2y$10$XWXfwld9PUeBqFUjJX1SgeX1HLt2o2WHx6xGanmMK9PVHDzPC6aRS', 3),
    (4, 'Carlos Ruiz', 'carlos@ejemplo.com', '$2y$10$XWXfwld9PUeBqFUjJX1SgeX1HLt2o2WHx6xGanmMK9PVHDzPC6aRS', 3),
    (5, 'Rosa Vega', 'rosa@ejemplo.com', '$2y$10$GFWpiU0WZ6fwlPLRxLp0lecglKneMqgLmIz2rFI/OxVMeQUf2klPa', 2);

-- ---------------------------------------------------------------
-- Integrantes del Grupo 04
-- Contraseña inicial para las cinco cuentas: Grupo04-2026!
-- ---------------------------------------------------------------
INSERT INTO usuarios (id, nombre, email, `contraseña`, rol_id) VALUES
    (6, 'Jorge Villamar Sánchez', 'jorge.villamar@ejemplo.com', '$2y$10$S8JNF39.xM7FftCYqzt30.IMWd0C4jaIHscMtlQCGzkcHGtQiCAWm', 1),
    (7, 'Norka Mideros Quiñonez', 'norka.mideros@ejemplo.com', '$2y$10$S8JNF39.xM7FftCYqzt30.IMWd0C4jaIHscMtlQCGzkcHGtQiCAWm', 2),
    (8, 'Bruce Balarezo Chacón', 'bruce.balarezo@ejemplo.com', '$2y$10$S8JNF39.xM7FftCYqzt30.IMWd0C4jaIHscMtlQCGzkcHGtQiCAWm', 3),
    (9, 'Mauro Chinlle Carrillo', 'mauro.chinlle@ejemplo.com', '$2y$10$S8JNF39.xM7FftCYqzt30.IMWd0C4jaIHscMtlQCGzkcHGtQiCAWm', 3),
    (10, 'Liseth Criollo', 'liseth.criollo@ejemplo.com', '$2y$10$S8JNF39.xM7FftCYqzt30.IMWd0C4jaIHscMtlQCGzkcHGtQiCAWm', 3);

INSERT INTO proyectos (id, nombre, descripcion, gerente_id) VALUES
    (1, 'Portal de solicitudes internas', 'Formulario en línea para que las áreas pidan materiales, accesos y mantenimiento.', 2),
    (2, 'Catálogo de proveedores', 'Registro de proveedores con datos de contacto, categoría y estado de validación.', 5);

INSERT INTO tareas (titulo, descripcion, estado, usuario_id, proyecto_id, actualizado_en) VALUES
    ('Revisar formulario de alta', 'Comprobar que los campos obligatorios y los mensajes de error se muestren bien en móvil.', 'Pendiente', 3, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
    ('Corregir validación de correo', 'El formulario acepta correos sin dominio. Ajustar la expresión regular y las pruebas.', 'En proceso', 4, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
    ('Documentar flujo de aprobación', 'Describir los pasos que sigue una solicitud desde que se envía hasta que se aprueba.', 'Completado', 3, 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
    ('Cargar proveedores de prueba', 'Registrar diez proveedores ficticios con categorías distintas para probar los filtros.', 'Pendiente', 4, 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
    ('Ajustar buscador de proveedores', 'La búsqueda por nombre debe ignorar tildes y mayúsculas.', 'En proceso', 3, 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
    ('Acta de reunión con compras', 'Resumir los acuerdos de la reunión del martes y compartirlos con el equipo.', 'Pendiente', 4, 2, DATE_SUB(NOW(), INTERVAL 5 DAY));
