<?php
session_start();
require_once __DIR__ . '/../Models/Comentario.php';
require_once __DIR__ . '/../Models/Censura.php';
require_once __DIR__ . '/../PDO/ComentarioPDO.php';
require_once __DIR__ . '/../PDO/CensuraPDO.php';

class ComentarioController
{
    private ComentarioPDO $comentarioPDO;
    private Censura $censura;
    private CensuraPDO $censuraPDO;

    public function __construct()
    {
        $this->comentarioPDO = new ComentarioPDO();
        $this->censuraPDO = new CensuraPDO();
        $this->censura = new Censura();
        
        // Cargar palabras prohibidas desde BD
        $palabras = $this->censuraPDO->obtenerPalabras();
        $this->censura->setPalabrasProhibidas($palabras);
    }

     // Crear comentario
    public function crearComentario(string $contenido, int $id_post, int $id_user): bool
    {
        if (trim($contenido) === "") {
            throw new InvalidArgumentException("El comentario no puede estar vacío");
        }

        // Censurar contenido
        $contenidoCensurado = $this->censura->censurarTexto($contenido);

        $comentario = new Comentario(0, $id_post, $id_user, $contenidoCensurado, date("Y-m-d H:i:s"), null);
        return $this->comentarioPDO->insertComentario($comentario);
    }

     // Obtener comentarios de un post
    public function obtenerComentarios(int $id_post): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $comentarios = $this->comentarioPDO->obtenerComentariosPorPost($id_post);
        
        $resultado = [];
        foreach ($comentarios as $com) {
            $resultado[] = [
                'id_comentario' => $com->getIdComentario(),
                'id_user' => $com->getIdUser(),
                'contenido' => $com->getContenido(),
                'usuario' => $com->getNombreUsuario(),
                'fecha_creacion' => $com->getFechaCreacion()
            ];
        }
        
        echo json_encode($resultado);
        exit;
    }


     // Eliminar comentario
    public function eliminarComentario(int $id_comentario, int $id_user, bool $esAdmin = false): bool
    {
        // Si es admin, puede eliminar cualquier comentario
        // Si no, solo puede eliminar sus propios
        if (!$esAdmin && !$this->comentarioPDO->isOwner($id_comentario, $id_user)) {
            throw new Exception("No tienes permiso para eliminar este comentario");
        }

        return $this->comentarioPDO->deleteComentario($id_comentario);
    }

     // Obtener lista de palabras prohibidas
    public function obtenerPalabrasProhibidas(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $palabras = $this->censuraPDO->obtenerPalabras();
            echo json_encode(['palabras' => $palabras]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

     // Agregar palabra prohibida
    public function agregarPalabraProhibida(string $palabra): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rol = $_SESSION['rol'] ?? 'user';
        if ($rol !== 'admin') {
            echo json_encode(['error' => 'No tienes permiso']);
            exit;
        }

        $palabra = trim($palabra);
        if (empty($palabra)) {
            echo json_encode(['error' => 'La palabra no puede estar vacía']);
            exit;
        }

        if (strlen($palabra) < 3) {
            echo json_encode(['error' => 'La palabra debe tener al menos 3 caracteres']);
            exit;
        }

        try {
            $this->censuraPDO->agregarPalabra($palabra);
            $this->censura->agregarPalabraProhibida($palabra);
            
            echo json_encode(['success' => 'Palabra agregada correctamente']);
        } catch (Exception $e) {
            echo json_encode(['error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
        }
        exit;
    }

     // Remover palabra prohibida
    public function removerPalabraProhibida(string $palabra): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rol = $_SESSION['rol'] ?? 'user';
        if ($rol !== 'admin') {
            echo json_encode(['error' => 'No tienes permiso']);
            exit;
        }

        $palabra = trim($palabra);
        if (empty($palabra)) {
            echo json_encode(['error' => 'La palabra no puede estar vacía']);
            exit;
        }

        try {
            $this->censuraPDO->removerPalabra($palabra);
            $this->censura->removerPalabraProhibida($palabra);
            
            echo json_encode(['success' => 'Palabra removida correctamente']);
        } catch (Exception $e) {
            echo json_encode(['error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
        }
        exit;
    }
}

// Router
// ----------------------
$controller = new ComentarioController();

// Crear comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear_comentario') {
    $id_user = $_SESSION['user_id'] ?? 0;
    $id_post = (int) ($_POST['id_post'] ?? 0);
    $contenido = htmlspecialchars($_POST['contenido'] ?? '', ENT_QUOTES, 'UTF-8');

    header('Content-Type: application/json; charset=utf-8');

    if ($id_user === 0) {
        echo json_encode(['error' => 'Debes iniciar sesión']);
        exit;
    }

    if ($id_post === 0 || empty($contenido)) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }

    try {
        if ($controller->crearComentario($contenido, $id_post, $id_user)) {
            echo json_encode(['success' => '✅ Comentario agregado']);
        } else {
            echo json_encode(['error' => 'Error al agregar comentario']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
    }
    exit;
}

// Obtener comentarios
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && (($_POST['action'] ?? $_GET['action'] ?? '') === 'obtener_comentarios')) {
    $id_post = (int) ($_POST['id_post'] ?? $_GET['id_post'] ?? 0);
    if ($id_post === 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'ID de post inválido']);
        exit;
    }
    $controller->obtenerComentarios($id_post);
    exit;
}

// Eliminar comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'eliminar_comentario') {
    $id_user = $_SESSION['user_id'] ?? 0;
    $rol = $_SESSION['rol'] ?? 'user';
    $id_comentario = (int) ($_POST['id_comentario'] ?? 0);

    header('Content-Type: application/json; charset=utf-8');

    if ($id_user === 0) {
        echo json_encode(['error' => 'Debes iniciar sesión']);
        exit;
    }

    if ($id_comentario === 0) {
        echo json_encode(['error' => 'ID de comentario inválido']);
        exit;
    }

    try {
        if ($controller->eliminarComentario($id_comentario, $id_user, $rol === 'admin')) {
            echo json_encode(['success' => '✅ Comentario eliminado']);
        } else {
            echo json_encode(['error' => 'Error al eliminar']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
    }
    exit;
}

// Obtener palabras prohibidas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'obtener_palabras') {
    $controller->obtenerPalabrasProhibidas();
    exit;
}

// Agregar palabra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'agregar_palabra') {
    $palabra = $_POST['palabra'] ?? '';
    $controller->agregarPalabraProhibida($palabra);
    exit;
}

// Remover palabra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remover_palabra') {
    $palabra = $_POST['palabra'] ?? '';
    $controller->removerPalabraProhibida($palabra);
    exit;
}
?>
