<?php
session_start(); // siempre al principio

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../PDO/UserPDO.php';

class UserController
{
    private UserPDO $userPDO;

    public function __construct()
    {
        $this->userPDO = new UserPDO();
    }

    // Registrar usuario (contraseña hasheada)
    public function registerUser(string $nombre, string $password): bool
    {
        if (trim($nombre) === "" || trim($password) === "") {
            throw new InvalidArgumentException("Nombre y contraseña no pueden estar vacíos");
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(0, $nombre, $hashedPassword);

        return $this->userPDO->insertUser($user);
    }

    // Login (comparación con password_verify)
    public function login(string $nombre, string $password): bool
    {
        $user = $this->userPDO->fetchUserByName($nombre);
        if ($user && password_verify($password, $user->getPassword())) {
            $_SESSION['user_id'] = $user->getIdUser();
            $_SESSION['nombre']  = $user->getNombre();
            $_SESSION['rol']     = $user->getRol();
            return true;
        }
        return false;
    }

    // Obtener perfil del usuario logueado
    public function getPerfil(): ?User
    {
        if (isset($_SESSION['user_id'])) {
            return $this->userPDO->fetchUserById($_SESSION['user_id']);
        }
        return null;
    }

    // Procesar formulario de login
    public function procesarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
            $nombre   = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
            $password = $_POST['password'];

            if ($this->login($nombre, $password)) {
                header("Location: ../views/HTML/Index.html?login=ok");
                exit;
            } else {
                header("Location: ../views/HTML/Login.html?login=fail");
                exit;
            }
        }
    }

    // Procesar formulario de registro
    public function procesarRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'registrar') {
            $nombre   = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
            $password = $_POST['password'];

            try {
                if ($this->registerUser($nombre, $password)) {
                    header("Location: ../views/HTML/Login.html?registro=ok");
                    exit;
                } else {
                    header("Location: ../views/HTML/Registro.html?registro=fail");
                    exit;
                }
            } catch (Exception $e) {
                header("Location: ../views/HTML/Registro.html?registro=fail");
                exit;
            }
        }
    }

    // Cerrar sesión
    public function logout(): void
    {
        session_destroy();
        header("Location: ../views/HTML/Login.html?logout=1");
        exit;
    }
}

// Ejecución directa según acción
$controller = new UserController();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action) {
    switch ($action) {
        case 'perfil':
            $user = $controller->getPerfil();
            header('Content-Type: application/json');
            if ($user) {
                echo json_encode([
                    "id_user" => $user->getIdUser(),
                    "nombre" => $user->getNombre(),
                    "Fecha_Registro" => $user->getFechaRegistro(),
                    "rol" => $user->getRol()
                ]);
            } else {
                echo json_encode(["error" => "No hay sesión activa"]);
            }
            break;

        case 'login':
            $controller->procesarLogin();
            break;

        case 'registrar':
            $controller->procesarRegistro();
            break;

        case 'logout':
            $controller->logout();
            break;
    }
}

