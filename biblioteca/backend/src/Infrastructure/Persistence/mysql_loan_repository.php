<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Port\Out\LoanRepository;
use App\Domain\Book\Book;
use \PDO;

class MySqlLoanRepository implements LoanRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cuenta cuántos préstamos activos tiene un socio (Regla RN-06)
     */
    public function countActiveLoansByMember(int $memberId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM loan WHERE member_id = :member_id AND status = 'ACTIVE'"
        );
        $stmt->execute(['member_id' => $memberId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Guarda el préstamo y descuenta 1 ejemplar disponible del libro en una transacción (Regla RN-04)
     */
    public function save(array $loanData, Book $book): array
    {
        try {
            // Iniciamos la transacción para asegurar consistencia
            $this->pdo->beginTransaction();

            // 1. Insertar el registro del préstamo
            $stmt = $this->pdo->prepare(
                "INSERT INTO loan (book_id, member_id, loan_date, due_date, status, created_at, updated_at)
                 VALUES (:book_id, :member_id, :loan_date, :due_date, :status, NOW(), NOW())"
            );
            $stmt->execute([
                'book_id' => $loanData['book_id'],
                'member_id' => $loanData['member_id'],
                'loan_date' => $loanData['loan_date'],
                'due_date' => $loanData['due_date'],
                'status' => $loanData['status']
            ]);

            $loanId = (int) $this->pdo->lastInsertId();

            // 2. Restar 1 al contador de ejemplares disponibles del libro
            $updateStmt = $this->pdo->prepare(
                "UPDATE book SET available_copies = available_copies - 1, updated_at = NOW() WHERE id = :id"
            );
            $updateStmt->execute(['id' => $book->id()]);

            // Confirmar transacción
            $this->pdo->commit();

            return [
                'id' => $loanId,
                'bookId' => $loanData['book_id'],
                'memberId' => $loanData['member_id'],
                'loanDate' => $loanData['loan_date'],
                'dueDate' => $loanData['due_date'],
                'status' => $loanData['status']
            ];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}