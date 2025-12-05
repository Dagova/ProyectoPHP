<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../BBDD/Conexion.php';

class UserPDO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getConexion();
    }

    // Insertar usuario (registro)
    public function insertUser(User $user): bool
    {
        // Comprobar si ya existe el nombre
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE nombre = :nombre");
        $check->execute([':nombre' => $user->getNombre()]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("El nombre de usuario ya está registrado");
        }

        // Insertar si no existe
        $stmt = $this->pdo->prepare("INSERT INTO users (nombre, password) VALUES (:nombre, :password)");
        return $stmt->execute([
            ':nombre'   => $user->getNombre(),
            ':password' => $user->getPassword()
        ]);
    }

    // Obtener usuario por ID (para perfil)
    public function fetchUserById(int $id): ?User
    {
        $sql = "SELECT id_user, nombre, password, Fecha_Registro, rol FROM users WHERE id_user = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new User($row['id_user'], $row['nombre'], $row['password'], $row['Fecha_Registro'], $row['rol'] ?? 'user') : null;
    }

    // Obtener usuario por nombre (para login)
    public function fetchUserByName(string $nombre): ?User
    {
        $sql = "SELECT id_user, nombre, password, Fecha_Registro, rol FROM users WHERE nombre = :nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new User($row['id_user'], $row['nombre'], $row['password'], $row['Fecha_Registro'], $row['rol'] ?? 'user') : null;
    }
}
