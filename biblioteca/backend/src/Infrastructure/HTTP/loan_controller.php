<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\RegisterLoanService;

class LoanController
{
    private RegisterLoanService $registerLoanService;

    public function __construct(RegisterLoanService $registerLoanService)
    {
        $this->registerLoanService = $registerLoanService;
    }

    /**
     * Endpoint para registrar un préstamo (POST /api/v1/loans)
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            // Ejecutar el caso de uso con las validaciones
            $loan = $this->registerLoanService->execute($input);

            http_response_code(201);
            header("Location: /api/v1/loans/" . $loan['id']);

            echo json_encode($loan);
        } catch (\InvalidArgumentException $e) {
            // Respuestas de error de negocio (404, 422, etc.)
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400;
            http_response_code($statusCode);

            echo json_encode([
                'title' => 'Error en el préstamo',
                'status' => $statusCode,
                'detail' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'title' => 'Error interno',
                'status' => 500,
                'detail' => $e->getMessage()
            ]);
        }
    }
}