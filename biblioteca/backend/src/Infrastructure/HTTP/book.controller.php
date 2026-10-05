<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateBookService;
use App\Application\UseCase\ListBooksService;

class BookController
{
    private CreateBookService $createBookService;
    private ListBooksService $listBooksService;

    public function __construct(
        CreateBookService $createBookService,
        ListBooksService $listBooksService
    ) {
        $this->createBookService = $createBookService;
        $this->listBooksService = $listBooksService;
    }

    /**
     * Endpoint para crear un libro (POST /api/v1/books)
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            // Leer el cuerpo de la petición en formato JSON
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            // Ejecutar el caso de uso de creación
            $book = $this->createBookService->execute($input);

            // Responder con éxito 201 Created e incluir la cabecera Location como pide la prueba
            http_response_code(201);
            header("Location: /api/v1/books/" . $book->id());

            echo json_encode([
                'id' => $book->id(),
                'isbn' => $book->isbn()->value(),
                'title' => $book->title(),
                'authorId' => $book->authorId(),
                'totalCopies' => $book->totalCopies(),
                'availableCopies' => $book->availableCopies()
            ]);
        } catch (\InvalidArgumentException $e) {
            // Capturar errores de validación de reglas de negocio (ej. 409 Conflicto o 422)
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400;
            http_response_code($statusCode);

            echo json_encode([
                'title' => 'Error de validación o regla de negocio',
                'status' => $statusCode,
                'detail' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint para listar libros con filtros y paginación (GET /api/v1/books)
     */
    public function index(): void
    {
        header('Content-Type: application/json');

        try {
            // Capturar parámetros de la URL (Query Parameters)
            $title = $_GET['title'] ?? null;
            $authorId = isset($_GET['authorId']) ? (int) $_GET['authorId'] : null;
            
            $available = null;
            if (isset($_GET['available'])) {
                $available = filter_var($_GET['available'], FILTER_VALIDATE_BOOLEAN);
            }

            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
            $size = isset($_GET['size']) ? max(1, (int) $_GET['size']) : 10;

            // Ejecutar el caso de uso de listado
            $result = $this->listBooksService->execute($title, $authorId, $available, $page, $size);

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'title' => 'Error interno del servidor',
                'status' => 500,
                'detail' => $e->getMessage()
            ]);
        }
    }
}