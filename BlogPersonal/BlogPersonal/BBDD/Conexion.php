<?php
class Conexion
{
    private static ?PDO $conexion = null;

    // Configuración de la base de datos
    private const DB_HOST = "localhost";
    private const DB_NAME = "blogpersonal";
    private const DB_USER = "root";
    private const DB_PASS = "";
    private const DB_CHARSET = "utf8mb4";

    // Método estático para obtener la conexión
    public static function getConexion(): PDO
    {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
                self::$conexion = new PDO($dsn, self::DB_USER, self::DB_PASS);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
?>
