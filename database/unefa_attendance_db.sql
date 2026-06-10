CREATE DATABASE IF NOT EXISTS `unefa_attendance_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `unefa_attendance_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-04:00";

-- MÓDULO B: ESTRUCTURA UNIVERSITARIA
CREATE TABLE `career` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `career_code` varchar(20) NOT NULL UNIQUE,
  `career_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `semester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester_number` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MÓDULO C: PLANIFICACIÓN ACADÉMICA
CREATE TABLE `subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL UNIQUE,
  `subject_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MÓDULO A: IDENTIDAD, SEGURIDAD Y ACCESO
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_number` varchar(20) NOT NULL UNIQUE,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `second_last_name` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `career_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`career_id`) REFERENCES `career`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Teacher','Student') NOT NULL,
  `status` enum('Active', 'Withdrawn', 'PendingApproval') NOT NULL DEFAULT 'PendingApproval',
  `force_password_change` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`profile_id`) REFERENCES `profile`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `qr_credential` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL UNIQUE,
  `qr_token` varchar(64) NOT NULL UNIQUE,
  `qr_status` enum('Active','Blocked') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MÓDULO C (Continuación): MALLA CURRICULAR Y SECCIONES
CREATE TABLE `pensum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `career_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_curriculum` (`subject_id`,`career_id`,`semester_id`),
  FOREIGN KEY (`subject_id`) REFERENCES `subject`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`career_id`) REFERENCES `career`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`semester_id`) REFERENCES `semester`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `default_delegate_id` int(11) DEFAULT NULL,
  `section_name` varchar(20) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `section_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`subject_id`) REFERENCES `subject`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`teacher_id`) REFERENCES `user`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`default_delegate_id`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enrollment_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `status` enum('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `section`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enrollment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `enrollment_status` enum('Pending', 'Active', 'Withdrawn') NOT NULL DEFAULT 'Pending',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `section`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MÓDULO D: CONTROL DE ASISTENCIA
CREATE TABLE `class_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL,
  `current_delegate_id` int(11) DEFAULT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `actual_end_time` time DEFAULT NULL,
  `closure_type` enum('Manual','Automatic','In Progress') NOT NULL DEFAULT 'In Progress',
  `session_type` enum('Regular','Extraordinary') NOT NULL DEFAULT 'Regular',
  `extraordinary_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`section_id`) REFERENCES `section`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`current_delegate_id`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_status` enum('Absent','Present','Excused','Withdrawn') NOT NULL DEFAULT 'Absent',
  `excuse_reason` text DEFAULT NULL,
  `registered_at` time DEFAULT NULL,
  `modification_source` enum('Scanned','Manual','System') NOT NULL DEFAULT 'System',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`session_id`) REFERENCES `class_session`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `user`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERCIONES INICIALES
-- INSERCIONES INICIALES DE ESTRUCTURA
INSERT INTO `career` (`id`, `career_code`, `career_name`) VALUES
(1, 'SIS01', 'Ingeniería de Sistemas'),
(2, 'ADM01', 'Administración de Desastres'),
(3, 'ECON01', 'Economía Social');

INSERT INTO `semester` (`id`, `semester_number`, `semester_name`) VALUES
(1, 1, 'Primer Semestre'),
(2, 2, 'Segundo Semestre');

-- INSERCIONES DE USUARIOS INICIALES (ADMIN Y TEST)
INSERT INTO `profile` (`id`, `id_number`, `first_name`, `last_name`, `career_id`) 
VALUES (1, 'V-00000000', 'Administrador', 'General', 1);

INSERT INTO `user` (`id`, `profile_id`, `email`, `password`, `role`, `status`, `force_password_change`) 
VALUES (1, 1, 'admin@unefa.edu.ve', '$2y$10$Wx2SlX4nRQeL1ZK4GKYhrOTR.gy3zUlBOmiN9i94oZTORcu0Hvb4m', 'Admin', 'Active', 0);

-- Profesor de prueba (pass: teacher123)
INSERT INTO `profile` (`id`, `id_number`, `first_name`, `last_name`, `career_id`) 
VALUES (2, 'V-11111111', 'Profesor', 'De Prueba', 1);

INSERT INTO `user` (`id`, `profile_id`, `email`, `password`, `role`, `status`, `force_password_change`) 
VALUES (2, 2, 'teacher@unefa.edu.ve', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher', 'Active', 0);

COMMIT;