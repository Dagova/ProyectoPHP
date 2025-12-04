-- ============================================
-- Script de Creación de Base de Datos
-- BlogPersonal - Todas las Tablas
-- ============================================

-- Crear base de datos (comentar si ya existe)
CREATE DATABASE IF NOT EXISTS blogpersonal 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE blogpersonal;

-- ============================================
-- Tabla: users
-- Gestión de usuarios del sistema
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
    Fecha_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rol ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: posts
-- Publicaciones del blog
-- ============================================
CREATE TABLE IF NOT EXISTS posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    INDEX idx_user (id_user),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: comentarios
-- Comentarios en publicaciones
-- ============================================
CREATE TABLE IF NOT EXISTS comentarios (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    id_user INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    INDEX idx_post (id_post),
    INDEX idx_user (id_user),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: palabras_prohibidas
-- Diccionario para sistema de censura
-- ============================================
CREATE TABLE IF NOT EXISTS palabras_prohibidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    palabra VARCHAR(255) UNIQUE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_palabra (palabra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar palabras prohibidas por defecto
-- ============================================
INSERT IGNORE INTO palabras_prohibidas (palabra) VALUES
('droga'),
('drogas'),
('matar'),
('abusar'),
('abuso'),
('violencia'),
('violento'),
('asesino'),
('asesinato'),
('cocaína'),
('heroína'),
('marihuana'),
('cannabis'),
('meth'),
('crack'),
('fentanilo'),
('tráfico'),
('narcotráfico'),
('sicario'),
('pandilla'),
('crimen'),
('criminal'),
('homicidio'),
('violación'),
('agresión'),
('tortura');

-- ============================================
-- Verificación de tablas creadas
-- ============================================
SHOW TABLES;

SELECT 
    'Tablas creadas correctamente' AS Status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'blogpersonal') AS Total_Tablas,
    (SELECT COUNT(*) FROM palabras_prohibidas) AS Palabras_Prohibidas;
