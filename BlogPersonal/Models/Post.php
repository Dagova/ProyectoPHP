<?php
class Post
{
    private int $id_post;
    private string $titulo;
    private string $contenido;
    private string $fecha_creacion;
    private int $id_user;

    public function __construct(int $id_post, string $titulo, string $contenido, string $fecha_creacion, int $id_user)
    {
        $this->id_post = $id_post;
        $this->titulo = $titulo;
        $this->contenido = $contenido;
        $this->fecha_creacion = $fecha_creacion;
        $this->id_user = $id_user;
    }

    public function getIdPost(): int { return $this->id_post; }
    public function getTitulo(): string { return $this->titulo; }
    public function getContenido(): string { return $this->contenido; }
    public function getFechaCreacion(): string { return $this->fecha_creacion; }
    public function getIdUser(): int { return $this->id_user; }
}
