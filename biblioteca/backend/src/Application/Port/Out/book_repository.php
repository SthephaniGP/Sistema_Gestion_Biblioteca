<?php
declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Book\Book;
use App\Domain\Book\Isbn;

interface BookRepository
{
    public function findById(int $id): ?Book;
    
    public function existsByIsbn(Isbn $isbn): bool;
    
    public function save(Book $book): Book;
    
    public function delete(int $id): void;
}
