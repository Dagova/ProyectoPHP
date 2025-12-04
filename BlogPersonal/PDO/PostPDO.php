<?php
require_once __DIR__ . '/../Models/Post.php';
require_once __DIR__ . '/../BBDD/Conexion.php';

class PostPDO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getConexion();
    }

    // Insertar nuevo post
    public function insertPost(Post $post): bool
    {
        $sql = "INSERT INTO posts (titulo, contenido, id_user) 
                VALUES (:titulo, :contenido, :id_user)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo'    => $post->getTitulo(),
            ':contenido' => $post->getContenido(),
            ':id_user'   => $post->getIdUser()
        ]);
    }

    // Obtener todos los posts con su autor (si lo necesitas en algún listado general)
    public function fetchAllPosts(): array
    {
        $sql = "SELECT p.id_post, p.titulo, p.contenido, p.fecha_creacion, u.nombre
                FROM posts p 
                INNER JOIN users u ON p.id_user = u.id_user 
                ORDER BY p.fecha_creacion DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener posts filtrados por nombre de usuario (sirve tanto para 'mis_posts' como para 'buscar_post')
    public function mis_posts(string $nombre): array
    {
        $sql = "SELECT p.id_post, p.titulo, p.contenido, p.fecha_creacion, u.nombre
                FROM posts p
                INNER JOIN users u ON p.id_user = u.id_user
                WHERE u.nombre = :nombre
                ORDER BY p.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar post por ID
    public function deletePost(int $id_post): bool
    {
        $sql = "DELETE FROM posts WHERE id_post = :id_post";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id_post' => $id_post]);
    }

    // Verificar que el usuario es propietario del post
    public function isOwner(int $id_post, int $id_user): bool
    {
        $sql = "SELECT COUNT(*) FROM posts WHERE id_post = :id_post AND id_user = :id_user";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_post' => $id_post, ':id_user' => $id_user]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
