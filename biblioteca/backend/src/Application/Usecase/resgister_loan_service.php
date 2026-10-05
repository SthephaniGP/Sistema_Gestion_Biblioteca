<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Port\Out\BookRepository;
use App\Application\Port\Out\LoanRepository;
use App\Application\Port\Out\MemberRepository;

class RegisterLoanService
{
    private BookRepository $bookRepository;
    private LoanRepository $loanRepository;
    private MemberRepository $memberRepository;

    public function __construct(
        BookRepository $bookRepository,
        LoanRepository $loanRepository,
        MemberRepository $memberRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->loanRepository = $loanRepository;
        $this->memberRepository = $memberRepository;
    }

    public function execute(array $requestData): array
    {
        $bookId = (int) $requestData['bookId'];
        $memberId = (int) $requestData['memberId'];

        // 1. Validar que el libro exista
        $book = $this->bookRepository->findById($bookId);
        if (!$book) {
            throw new \InvalidArgumentException("El libro solicitado no existe.", 404);
        }

        // 2. Validar que el socio exista
        $member = $this->memberRepository->findById($memberId);
        if (!$member) {
            throw new \InvalidArgumentException("El socio solicitado no existe.", 404);
        }

        // 3. Regla de negocio RN-05: Un socio inactivo no puede solicitar préstamos
        if (!$member->isActive()) {
            throw new \InvalidArgumentException("El socio se encuentra inactivo y no puede solicitar préstamos.", 422);
        }

        // 4. Regla de negocio RN-04: Solo se puede prestar un libro si tiene al menos un ejemplar disponible
        if ($book->availableCopies() <= 0) {
            throw new \InvalidArgumentException("El libro no tiene ejemplares disponibles para préstamo.", 422);
        }

        // 5. Regla de negocio RN-06: Un socio puede tener como máximo 3 préstamos activos al mismo tiempo
        $activeLoansCount = $this->loanRepository->countActiveLoansByMember($memberId);
        if ($activeLoansCount >= 3) {
            throw new \InvalidArgumentException("El socio ya cuenta con el límite máximo de 3 préstamos activos.", 422);
        }

        // 6. Fechas del préstamo (RN-07: La fecha de devolución esperada es 14 días después)
        $loanDate = new \DateTime();
        $dueDate = (clone $loanDate)->modify('+14 days');

        // 7. Registrar el préstamo y actualizar los ejemplares disponibles del libro
        // (Nota: Idealmente esto se envuelve en una transacción o Unit of Work)
        $loanData = [
            'book_id' => $bookId,
            'member_id' => $memberId,
            'loan_date' => $loanDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'return_date' => null,
            'status' => 'ACTIVE'
        ];

        return $this->loanRepository->save($loanData, $book);
    }
}