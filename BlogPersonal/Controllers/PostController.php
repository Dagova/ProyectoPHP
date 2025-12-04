<?php
session_start();
require_once __DIR__ . '/../Models/Post.php';
require_once __DIR__ . '/../Models/Censura.php';
require_once __DIR__ . '/../PDO/PostPDO.php';
require_once __DIR__ . '/../PDO/CensuraPDO.php';

class PostController
{
    public PostPDO $postPDO;
    public Censura $censura;
    public CensuraPDO $censuraPDO;

    public function __construct()
    {
        $this->postPDO = new PostPDO();
        $this->censuraPDO = new CensuraPDO();
        $this->censura = new Censura();
        
        // Cargar palabras prohibidas desde BD
        $palabras = $this->censuraPDO->obtenerPalabras();
        $this->censura->setPalabrasProhibidas($palabras);
    }

    // Crear post
    public function crearPost(string $titulo, string $contenido, int $id_user): bool
    {
        if (trim($titulo) === "" || trim($contenido) === "") {
            throw new InvalidArgumentException("El título y el contenido no pueden estar vacíos");
        }

        // Censurar título y contenido
        $tituloCensurado = $this->censura->censurarTexto($titulo);
        $contenidoCensurado = $this->censura->censurarTexto($contenido);

        $post = new Post(0, $tituloCensurado, $contenidoCensurado, date("Y-m-d H:i:s"), $id_user);
        return $this->postPDO->insertPost($post);
    }

    // Ver mis posts (usando nombre de sesión)
    public function verMisPosts(): void
    {
        $nombre = $_SESSION['nombre'] ?? '';
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->postPDO->mis_posts($nombre));
        exit;
    }

    // Buscar posts de otro usuario por nombre
    public function buscarPostsDeUsuario(string $usuario): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->postPDO->mis_posts($usuario));
        exit;
    }

    // Eliminar post
    public function eliminarPost(int $id_post, int $id_user, bool $esAdmin = false): bool
    {
        // Si es admin, puede eliminar cualquier post
        // Si es usuario normal, solo puede eliminar sus propios posts
        if (!$esAdmin && !$this->postPDO->isOwner($id_post, $id_user)) {
            throw new Exception("No tienes permiso para eliminar este post");
        }

        return $this->postPDO->deletePost($id_post);
    }

    // Obtener todos los posts (para admin)
    public function obtenerTodosPosts(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->postPDO->fetchAllPosts());
        exit;
    }
}

// Router
$controller = new PostController();

// Crear post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear_post') {
    $id_user = $_SESSION['user_id'] ?? 0;

    if ($id_user === 0) {
        echo "<div class='alert alert-error'>⚠️ Debes iniciar sesión para crear un post</div>";
        exit;
    }

    $titulo    = htmlspecialchars($_POST['titulo'], ENT_QUOTES, 'UTF-8');
    $contenido = htmlspecialchars($_POST['contenido'], ENT_QUOTES, 'UTF-8');

    try {
        // Detectar palabras prohibidas
        $palabrasEnTitulo = $controller->censura->detectarPalabrasProhibidas($titulo);
        $palabrasEnContenido = $controller->censura->detectarPalabrasProhibidas($contenido);
        $todasLasPalabras = array_merge($palabrasEnTitulo, $palabrasEnContenido);

        if (!empty($todasLasPalabras)) {
            $palabrasDetectadas = implode(", ", array_unique($todasLasPalabras));
            echo "<div class='alert alert-error'>⚠️ Contenido no permitido detectado. Las siguientes palabras han sido censuradas: <strong>" . htmlspecialchars($palabrasDetectadas, ENT_QUOTES, 'UTF-8') . "</strong></div>";
        }

        if ($controller->crearPost($titulo, $contenido, $id_user)) {
            echo "<div class='alert alert-success'>✅ Post creado correctamente</div>";
        } else {
            echo "<div class='alert alert-error'>⚠️ Error al crear el post</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-error'>⚠️ " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
    }
    exit;
}

// Ver mis posts
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'mis_posts') {
    $controller->verMisPosts();
    exit;
}

// Obtener todos los posts (para admin)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'todos_posts') {
    $controller->obtenerTodosPosts();
    exit;
}

// Buscar posts de otro usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'buscar_post') {
    $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');
    $controller->buscarPostsDeUsuario($usuario);
    exit;
}

// Eliminar post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'eliminar_post') {
    $id_user = $_SESSION['user_id'] ?? 0;
    $rol = $_SESSION['rol'] ?? 'user';
    $id_post = (int) ($_POST['id_post'] ?? 0);
    $esAdmin = ($rol === 'admin');

    header('Content-Type: application/json; charset=utf-8');

    if ($id_user === 0) {
        echo json_encode(['error' => 'Debes iniciar sesión']);
        exit;
    }

    if ($id_post === 0) {
        echo json_encode(['error' => 'ID de post inválido']);
        exit;
    }

    try {
        if ($controller->eliminarPost($id_post, $id_user, $esAdmin)) {
            echo json_encode(['success' => '✅ Post eliminado correctamente']);
        } else {
            echo json_encode(['error' => '⚠️ Error al eliminar el post']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => '⚠️ ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
    }
    exit;
}