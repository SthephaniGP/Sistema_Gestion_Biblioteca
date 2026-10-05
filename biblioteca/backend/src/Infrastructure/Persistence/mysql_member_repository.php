<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Port\Out\MemberRepository;
use App\Domain\Member\Member;
use \PDO;

class MySqlMemberRepository implements MemberRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Busca un socio por su ID en la base de datos
     */
    public function findById(int $id): ?Member
    {
        $stmt = $this->pdo->prepare("SELECT * FROM member WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Member(
            (int) $data['id'],
            $data['name'],
            $data['email'],
            (bool) $data['active']
        );
    }
}