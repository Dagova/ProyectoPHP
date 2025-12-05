<?php

class Censura
{
    // Lista de palabras prohibidas (case-insensitive)
    private array $palabrasProhibidas = [];

    private string $replacementChar = '*';

    public function __construct()
    {
        // Constructor vacío - las palabras se cargan desde BD
    }

    /**
     * Establecer palabras prohibidas desde BD
     */
    public function setPalabrasProhibidas(array $palabras): void
    {
        $this->palabrasProhibidas = array_map('strtolower', $palabras);
    }

    // Obtener la lista de palabras prohibidas
    public function getPalabrasProhibidas(): array
    {
        return $this->palabrasProhibidas;
    }

    // Agregar una nueva palabra prohibida
    public function agregarPalabraProhibida(string $palabra): void
    {
        $palabra = strtolower(trim($palabra));
        if (!in_array($palabra, $this->palabrasProhibidas)) {
            $this->palabrasProhibidas[] = $palabra;
        }
    }

    // Remover una palabra prohibida
    public function removerPalabraProhibida(string $palabra): void
    {
        $palabra = strtolower(trim($palabra));
        $this->palabrasProhibidas = array_filter(
            $this->palabrasProhibidas,
            fn($p) => $p !== $palabra
        );
    }

    /**
     * Detectar si un texto contiene palabras prohibidas
     * Retorna array con las palabras encontradas
     */
    public function detectarPalabrasProhibidas(string $texto): array
    {
        $textoLower = strtolower($texto);
        $encontradas = [];

        foreach ($this->palabrasProhibidas as $palabra) {
            // Buscar la palabra con límites de palabra
            if (preg_match("/\b" . preg_quote($palabra, '/') . "\b/i", $texto)) {
                if (!in_array($palabra, $encontradas)) {
                    $encontradas[] = $palabra;
                }
            }
        }

        return $encontradas;
    }

    /**
     * Censurar palabras prohibidas en un texto
     * Las reemplaza con asteriscos manteniendo la longitud
     */
    public function censurarTexto(string $texto): string
    {
        $textoCensurado = $texto;

        foreach ($this->palabrasProhibidas as $palabra) {
            // Reemplazar la palabra manteniendo su longitud con asteriscos
            $asteriscos = str_repeat($this->replacementChar, strlen($palabra));
            $textoCensurado = preg_replace(
                "/\b" . preg_quote($palabra, '/') . "\b/i",
                $asteriscos,
                $textoCensurado
            );
        }

        return $textoCensurado;
    }

    // Verificar si un texto es seguro (no contiene palabras prohibidas)
    public function esSeguro(string $texto): bool
    {
        return empty($this->detectarPalabrasProhibidas($texto));
    }

    // Establecer el carácter de reemplazo para la censura
    public function setReplacementChar(string $char): void
    {
        $this->replacementChar = $char[0] ?? '*';
    }
}
?>