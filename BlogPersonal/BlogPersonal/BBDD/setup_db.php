<?php
// Script para crear tablas necesarias

require 'BBDD/Conexion.php';

try {
    $pdo = Conexion::getConexion();
    
    // Crear tabla de comentarios
    echo "Creando tabla de comentarios...\n";
    $sql = "CREATE TABLE IF NOT EXISTS comentarios (
        id_comentario INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT NOT NULL,
        id_user INT NOT NULL,
        contenido TEXT NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE,
        FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Tabla comentarios creada\n";
    
    // Crear tabla de palabras prohibidas
    echo "\nCreando tabla de palabras prohibidas...\n";
    $sql = "CREATE TABLE IF NOT EXISTS palabras_prohibidas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        palabra VARCHAR(255) UNIQUE NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Tabla palabras_prohibidas creada\n";
    
    // Insertar palabras por defecto
    echo "\nInsertando palabras prohibidas por defecto...\n";
    $palabras = [
        'droga', 'drogas', 'matar', 'abusar', 'abuso',
        'violencia', 'violento', 'asesino', 'asesinato',
        'cocaína', 'heroína', 'marihuana', 'cannabis',
        'meth', 'crack', 'fentanilo',
        'tráfico', 'narcotráfico', 'sicario', 'pandilla',
        'crimen', 'criminal', 'homicidio', 'violación',
        'agresión', 'tortura'
    ];
    
    $sql = "INSERT IGNORE INTO palabras_prohibidas (palabra) VALUES (:palabra)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($palabras as $palabra) {
        $stmt->execute([':palabra' => strtolower($palabra)]);
    }
    
    $result = $pdo->query("SELECT COUNT(*) FROM palabras_prohibidas");
    $count = $result->fetchColumn();
    echo "✅ $count palabras prohibidas insertadas\n";
    
    echo "\n✅ Setup completado exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
