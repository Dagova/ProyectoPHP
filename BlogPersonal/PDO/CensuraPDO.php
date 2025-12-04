<?php
// PDO/CensuraPDO.php - Acceso a datos para palabras prohibidas

require_once __DIR__ . '/../BBDD/Conexion.php';

class CensuraPDO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getConexion();
    }

     // Obtener todas las palabras prohibidas de la BD
    public function obtenerPalabras(): array
    {
        try {
            $sql = "SELECT palabra FROM palabras_prohibidas ORDER BY palabra ASC";
            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $resultado ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

     // Agregar palabra prohibida a la BD
    public function agregarPalabra(string $palabra): bool
    {
        $palabra = strtolower(trim($palabra));
        
        // Verificar si ya existe
        $sql = "SELECT COUNT(*) FROM palabras_prohibidas WHERE palabra = :palabra";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':palabra' => $palabra]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("La palabra ya existe en la lista");
        }

        // Insertar nueva palabra
        $sql = "INSERT INTO palabras_prohibidas (palabra) VALUES (:palabra)";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([':palabra' => $palabra]);
        
        return $resultado;
    }

     // Remover palabra prohibida de la BD
    public function removerPalabra(string $palabra): bool
    {
        $palabra = strtolower(trim($palabra));
        
        $sql = "DELETE FROM palabras_prohibidas WHERE palabra = :palabra";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([':palabra' => $palabra]);
        
        return $resultado;
    }
}
?>
