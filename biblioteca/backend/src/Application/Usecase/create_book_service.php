<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Book\Book;
use App\Domain\Book\Isbn;
use App\Application\Port\Out\BookRepository;

class CreateBookService
{
    private BookRepository $bookRepository;

    // Inyectamos el puerto de salida (repositorio) por el constructor
    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function execute(array $requestData): Book
    {
        // 1. Creamos y validamos el Objeto de Valor ISBN (RN-01)
        $isbn = new Isbn($requestData['isbn']);

        // 2. Verificamos la regla de negocio: El ISBN debe ser único en el catálogo (RN-01 / Código 409)
        if ($this->bookRepository->existsByIsbn($isbn)) {
            throw new \InvalidArgumentException("Ya existe un libro registrado con este ISBN.", 409);
        }

        // 3. Al crear un libro nuevo, los ejemplares disponibles iniciales son iguales al total (RN-03)
        $totalCopies = (int) $requestData['totalCopies'];
        $availableCopies = $totalCopies;

        // 4. Creamos la entidad pura de dominio Book
        $book = new Book(
            null, // El ID es nulo porque la base de datos lo generará autoincrementable
            $isbn,
            $requestData['title'],
            (int) $requestData['authorId'],
            isset($requestData['publicationYear']) ? (int) $requestData['publicationYear'] : null,
            $totalCopies,
            $availableCopies
        );

        // 5. Guardamos a través del puerto de salida y retornamos el libro creado
        return $this->bookRepository->save($book);
    }
}