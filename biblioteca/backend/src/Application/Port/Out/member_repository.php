<?php
declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Member\Member;

interface MemberRepository
{
    public function findById(int $id): ?Member;
}