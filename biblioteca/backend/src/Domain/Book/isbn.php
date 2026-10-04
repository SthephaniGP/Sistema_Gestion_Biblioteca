<?php
declare(strict_types=1);

namespace App\Domain\Book;

class Isbn
{
    private string $value;

    public function __construct(string $value)
    {
        $cleaned = trim($value);
        // Validar longitud de 10 o 13 dígitos
        if (strlen($cleaned) !== 10 && strlen($cleaned) !== 13) {
            throw new \InvalidArgumentException("El ISBN debe tener exactamente 10 o 13 dígitos.", 422);
        }
        $this->value = $cleaned;
    }

    public function value(): string
    {
        return $this->value;
    }
}