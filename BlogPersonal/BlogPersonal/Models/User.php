<?php
class User
{
    private int $id_user;
    private string $nombre;
    private string $password;
    private ?string $fecha_registro;
    private string $rol;

    public function __construct(int $id_user, string $nombre, string $password, ?string $fecha_registro = null, string $rol = 'user')
    {
        $this->id_user = $id_user;
        $this->nombre = $nombre;
        $this->password = $password;
        $this->fecha_registro = $fecha_registro;
        $this->rol = $rol;
    }

    public function getIdUser(): int { return $this->id_user; }
    public function getNombre(): string { return $this->nombre; }
    public function getPassword(): string { return $this->password; }
    public function getFechaRegistro(): ?string { return $this->fecha_registro; }
    public function getRol(): string { return $this->rol; }
    public function isAdmin(): bool { return $this->rol === 'admin'; }
}
