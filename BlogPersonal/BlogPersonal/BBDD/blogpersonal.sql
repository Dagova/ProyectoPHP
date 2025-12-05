-- Crear base de datos (si no existe)
DROP DATABASE IF EXISTS blogpersonal;
CREATE DATABASE IF NOT EXISTS blogpersonal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE blogpersonal;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'user' NOT NULL,
    Fecha_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de posts
CREATE TABLE IF NOT EXISTS posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    CONSTRAINT fk_posts_users FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de palabras baneadas
CREATE TABLE IF NOT EXISTS palabras_prohibidas (
    id_palabra INT AUTO_INCREMENT PRIMARY KEY,
    palabra VARCHAR(100) NOT NULL UNIQUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de comentarios
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

-- Se crea usuario admin
INSERT INTO users (nombre, password, rol, Fecha_Registro) 
VALUES ('admin', '$2y$10$czNEjXe9SYR9mHmnaN4TT.TkM0NoojM79ZNW90B7ss63iv654Fyz6', 'admin', NOW())
ON DUPLICATE KEY UPDATE rol='admin';

INSERT INTO palabras_prohibidas (palabra) VALUES
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

select * from palabras_prohibidas;
SELECT * FROM USERS;
select * from posts;