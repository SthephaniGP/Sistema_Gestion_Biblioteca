<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Port\Out\BookRepository;

class ListBooksService
{
    private BookRepository $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Lista los libros aplicando paginación y filtros opcionales.
     */
    public function execute(?string $title, ?int $authorId, ?bool $available, int $page, int $size): array
    0    {
        // Validar que la paginación sea correcta por seguridad
        $page = max(1, $page);
        $size = max(1, min(100, $size)); // Límite máximo de seguridad por página

        // Llamamos al repositorio de salida para obtener los resultados paginados
        return $this->bookRepository->search($title, $authorId, $available, $page, $size);
    }
}