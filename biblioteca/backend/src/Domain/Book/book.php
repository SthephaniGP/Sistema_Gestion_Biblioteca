<?php
declare(strict_types=1);

namespace App\Domain\Book;

class Book
{
    private ?int $id;
    private Isbn $isbn;
    private string $title;
    private int $authorId;
    private ?int $publicationYear;
    private int $totalCopies;
    private int $availableCopies;

    public function __construct(
        ?int $id,
        Isbn $isbn,
        string $title,
        int $authorId,
        ?int $publicationYear,
        int $totalCopies,
        int $availableCopies
    ) {
        if ($availableCopies < 0 || $availableCopies > $totalCopies) {
            throw new \InvalidArgumentException("Los ejemplares disponibles no pueden ser negativos ni superar el total.", 422);
        }

        $this->id = $id;
        $this->isbn = $isbn;
        $this->title = $title;
        $this->authorId = $authorId;
        $this->publicationYear = $publicationYear;
        $this->totalCopies = $totalCopies;
        $this->availableCopies = $availableCopies;
    }

    public function id(): ?int { return $this->id; }
    public function isbn(): Isbn { return $this->isbn; }
    public function title(): string { return $this->title; }
    public function authorId(): int { return $this->authorId; }
    public function totalCopies(): int { return $this->totalCopies; }
    public function availableCopies(): int { return $this->availableCopies; }
}