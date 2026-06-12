-- ============================================
-- SISTEMA DE GESTIÓN DE ASISTENCIAS - UNEFA
-- BASE DE DATOS SEGURA (VERSIÓN EN ESPAÑOL)
-- ============================================

DROP DATABASE IF EXISTS `unefa_asistencias_db`;
CREATE DATABASE `unefa_asistencias_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `unefa_asistencias_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-04:00";

-- ============================================
-- SEGURIDAD: ROLES Y PERMISOS
-- ============================================

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL UNIQUE,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave_permiso` varchar(100) NOT NULL UNIQUE,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permisos_rol` (
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  PRIMARY KEY (`id_rol`, `id_permiso`),
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEGURIDAD: PREGUNTAS DE RECUPERACIÓN
-- ============================================

CREATE TABLE `preguntas_seguridad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `texto_pregunta` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ESTRUCTURA UNIVERSITARIA
-- ============================================

CREATE TABLE `carrera` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_carrera` varchar(20) NOT NULL UNIQUE,
  `nombre_carrera` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `semestre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_semestre` int(11) NOT NULL,
  `nombre_semestre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `materia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_materia` varchar(20) NOT NULL UNIQUE,
  `nombre_materia` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- IDENTIDAD Y ACCESO
-- ============================================

CREATE TABLE `perfil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL UNIQUE,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `id_carrera` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_carrera`) REFERENCES `carrera`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL UNIQUE,
  `id_rol` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL UNIQUE,
  `clave` varchar(255) NOT NULL,
  `estado` enum('Activo', 'Retirado', 'Bloqueado', 'Pendiente') NOT NULL DEFAULT 'Pendiente',
  `intentos_fallidos` tinyint(2) NOT NULL DEFAULT 0,
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `cambio_clave_obligatorio` tinyint(1) NOT NULL DEFAULT 1,
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_perfil`) REFERENCES `perfil`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `respuestas_seguridad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `respuesta_cifrada` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas_seguridad`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `credencial_qr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL UNIQUE,
  `token_qr` varchar(64) NOT NULL UNIQUE,
  `estado_qr` enum('Activo','Bloqueado') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- AUDITORÍA
-- ============================================

CREATE TABLE `registro_auditoria` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `nombre_tabla` varchar(50) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `valores_anteriores` json DEFAULT NULL,
  `valores_nuevos` json DEFAULT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PLANIFICACIÓN Y SECCIONES
-- ============================================

CREATE TABLE `pensum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_materia` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `id_semestre` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pensum_unico` (`id_materia`,`id_carrera`,`id_semestre`),
  FOREIGN KEY (`id_materia`) REFERENCES `materia`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_carrera`) REFERENCES `carrera`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_semestre`) REFERENCES `semestre`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_materia` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `id_delegado_predeterminado` int(11) DEFAULT NULL,
  `nombre_seccion` varchar(20) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado_seccion` enum('Activa','Inactiva') NOT NULL DEFAULT 'Activa',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_materia`) REFERENCES `materia`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_profesor`) REFERENCES `usuario`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_delegado_predeterminado`) REFERENCES `usuario`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inscripcion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `estado_inscripcion` enum('Pendiente', 'Activa', 'Retirada') NOT NULL DEFAULT 'Pendiente',
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_estudiante`) REFERENCES `usuario`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_seccion`) REFERENCES `seccion`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CONTROL DE ASISTENCIA
-- ============================================

CREATE TABLE `sesion_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_seccion` int(11) NOT NULL,
  `id_delegado_actual` int(11) DEFAULT NULL,
  `fecha_sesion` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin_real` time DEFAULT NULL,
  `tipo_cierre` enum('Manual','Automatico','En Progreso') NOT NULL DEFAULT 'En Progreso',
  `tipo_sesion` enum('Regular','Extraordinaria') NOT NULL DEFAULT 'Regular',
  `motivo_extraordinaria` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_seccion`) REFERENCES `seccion`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_delegado_actual`) REFERENCES `usuario`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sesion` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `estado_asistencia` enum('Inasistente','Presente','Justificado','Retirado') NOT NULL DEFAULT 'Inasistente',
  `motivo_justificativo` text DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `origen_modificacion` enum('Escaneado','Manual','Sistema') NOT NULL DEFAULT 'Sistema',
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asistencia_unica` (`id_sesion`, `id_estudiante`),
  FOREIGN KEY (`id_sesion`) REFERENCES `sesion_clase`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_estudiante`) REFERENCES `usuario`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INICIALIZACIÓN DE DATOS
START TRANSACTION;

-- Roles
INSERT INTO `roles` (`id`, `nombre_rol`, `descripcion`) VALUES
(1, 'Admin', 'Administración y Gestión de Profesores'),
(2, 'Profesor', 'Docente'),
(3, 'Estudiante', 'Alumno');

-- Preguntas de Seguridad
INSERT INTO `preguntas_seguridad` (`texto_pregunta`) VALUES
('¿Cuál es el nombre de tu primera mascota?'),
('¿Cuál es el apellido de soltera de tu madre?'),
('¿En qué mes naciste?'),
('¿Cuál es tu comida favorita?'),
('¿Cual es el primer nombre de tu abuelo?'),
('¿Cuál es el segundo nombre de tu padre?');

-- Perfil Admin
INSERT INTO `perfil` (`id`, `cedula`, `primer_nombre`, `primer_apellido`) 
VALUES (1, 'V-00000000', 'Administrador', 'Sistema');

-- Usuario Admin (clave: 123456)
INSERT INTO `usuario` (`id`, `id_perfil`, `id_rol`, `correo`, `clave`, `estado`, `cambio_clave_obligatorio`) 
VALUES (1, 1, 1, 'admin@unefa.edu.ve', '$2y$10$Wx2SlX4nRQeL1ZK4GKYhrOTR.gy3zUlBOmiN9i94oZTORcu0Hvb4m', 'Activo', 0);

COMMIT;
