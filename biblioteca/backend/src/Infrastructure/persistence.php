<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Port\Out\BookRepository;
use App\Domain\Book\Book;
use App\Domain\Book\Isbn;
\PDO;

class MySqlBookRepository implements BookRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Book
    {
        $stmt = $this->pdo->prepare("SELECT * FROM book WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToDomain($data);
    }

    public function existsByIsbn(Isbn $isbn): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM book WHERE isbn = :isbn");
        $stmt->execute(['isbn' => $isbn->value()]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function save(Book $book): Book
    {
        if ($book->id() === null) {
            // Insertar nuevo libro
            $stmt = $this->pdo->prepare(
                "INSERT INTO book (isbn, title, author_id, publication_year, total_copies, available_copies, created_at, updated_at) 
                 VALUES (:isbn, :title, :author_id, :publication_year, :total_copies, :available_copies, NOW(), NOW())"
            );
            $stmt->execute([
                'isbn' => $book->isbn()->value(),
                'title' => $book->title(),
                'author_id' => $book->authorId(),
                'publication_year' => null, // Puedes ajustar si la entidad incluye el año
                'total_copies' => $book->totalCopies(),
                'available_copies' => $book->availableCopies()
            ]);

            $id = (int) $this->pdo->lastInsertId();
            return new Book(
                $id,
                $book->isbn(),
                $book->title(),
                $book->authorId(),
                null,
                $book->totalCopies(),
                $book->availableCopies()
            );
        } else {
            // Actualizar libro existente
            $stmt = $this->pdo->prepare(
                "UPDATE book SET title = :title, author_id = :author_id, total_copies = :total_copies, 
                 available_copies = :available_copies, updated_at = NOW() WHERE id = :id"
            );
            $stmt->execute([
                'id' => $book->id(),
                'title' => $book->title(),
                'author_id' => $book->authorId(),
                'total_copies' => $book->totalCopies(),
                'available_copies' => $book->availableCopies()
            ]);

            return $book;
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM book WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function search(?string $title, ?int $authorId, ?bool $available, int $page, int $size): array
    {
        $offset = ($page - 1) * $size;
        $where = ["1=1"];
        $params = [];

        if (!empty($title)) {
            $where[] = "title LIKE :title";
            $params['title'] = "%{$title}%";
        }

        if ($authorId !== null) {
            $where[] = "author_id = :author_id";
            $params['author_id'] = $authorId;
        }

        if ($available !== null) {
            if ($available) {
                $where[] = "available_copies > 0";
            } else {
                $where[] = "available_copies = 0";
            }
        }

        $whereSql = implode(" AND ", $where);

        // Contar total de elementos para la paginación
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM book WHERE {$whereSql}");
        $countStmt->execute($params);
        $totalItems = (int) $countStmt->fetchColumn();

        // Obtener elementos paginados
        $sql = "SELECT * FROM book WHERE {$whereSql} LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $size, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $items = array_map([$this, 'mapToDomain'], $rows);

        $totalPages = ceil($totalItems / $size);

        return [
            'items' => $items,
            'page' => $page,
            'size' => $size,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages > 0 ? $totalPages : 1
        ];
    }

    /**
     * Mapeo de registro de base de datos a Entidad de Dominio
     */
    private function mapToDomain(array $data): Book
    {
        return new Book(
            (int) $data['id'],
            new Isbn($data['isbn']),
            $data['title'],
            (int) $data['author_id'],
            isset($data['publication_year']) ? (int) $data['publication_year'] : null,
            (int) $data['total_copies'],
            (int) $data['available_copies']
        );
    }
}