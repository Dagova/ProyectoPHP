<?php
class Comentario
{
    private int $id_comentarios;
    private int $id_post;
    private int $id_user;
    private string $contenido;
    private string $fecha_creacion;
    private ?string $nombre_usuario;

    public function __construct(
        int $id_comentarios,
        int $id_post,
        int $id_user,
        string $contenido,
        string $fecha_creacion,
        ?string $nombre_usuario = null
    ) {
        $this->id_comentarios = $id_comentarios;
        $this->id_post = $id_post;
        $this->id_user = $id_user;
        $this->contenido = $contenido;
        $this->fecha_creacion = $fecha_creacion;
        $this->nombre_usuario = $nombre_usuario;
    }

    public function getIdComentario(): int { return $this->id_comentarios; }
    public function getIdPost(): int { return $this->id_post; }
    public function getIdUser(): int { return $this->id_user; }
    public function getContenido(): string { return $this->contenido; }
    public function getFechaCreacion(): string { return $this->fecha_creacion; }
    public function getNombreUsuario(): ?string { return $this->nombre_usuario; }
}
?>
