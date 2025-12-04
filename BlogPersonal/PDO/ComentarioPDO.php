<?php
require_once __DIR__ . '/../Models/Comentario.php';
require_once __DIR__ . '/../BBDD/Conexion.php';

class ComentarioPDO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getConexion();
    }

    // Insertar comentario
    public function insertComentario(Comentario $comentario): bool
    {
        $sql = "INSERT INTO comentarios (id_post, id_user, contenido) 
                VALUES (:id_post, :id_user, :contenido)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id_post'   => $comentario->getIdPost(),
            ':id_user'   => $comentario->getIdUser(),
            ':contenido' => $comentario->getContenido()
        ]);
    }

     // Obtener comentarios de un post
    public function obtenerComentariosPorPost(int $id_post): array
    {
        $sql = "SELECT c.id_comentario, c.id_post, c.id_user, c.contenido, 
                       c.fecha_creacion, u.nombre
                FROM comentarios c
                INNER JOIN users u ON c.id_user = u.id_user
                WHERE c.id_post = :id_post
                ORDER BY c.fecha_creacion ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_post' => $id_post]);
        
        $resultado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[] = new Comentario(
                $row['id_comentario'],
                $row['id_post'],
                $row['id_user'],
                $row['contenido'],
                $row['fecha_creacion'],
                $row['nombre']
            );
        }
        return $resultado;
    }


     // Eliminar comentario

    public function deleteComentario(int $id_comentario): bool
    {
        $sql = "DELETE FROM comentarios WHERE id_comentario = :id_comentario";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id_comentario' => $id_comentario]);
    }

     // Verificar que el usuario es propietario del comentario
    public function isOwner(int $id_comentario, int $id_user): bool
    {
        $sql = "SELECT COUNT(*) FROM comentarios 
                WHERE id_comentario = :id_comentario AND id_user = :id_user";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_comentario' => $id_comentario, ':id_user' => $id_user]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
?>
