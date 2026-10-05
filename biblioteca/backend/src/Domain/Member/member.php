<?php
declare(strict_types=1);

namespace App\Domain\Member;

class Member
{
    private ?int $id;
    private string $name;
    private string $email;
    private bool $active;

    public function __construct(?int $id, string $name, string $email, bool $active = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->active = $active;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}