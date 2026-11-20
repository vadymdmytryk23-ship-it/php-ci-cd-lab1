<?php

declare(strict_types=1);

namespace App\Tasks;

class Category
{
    private array $books = [];

    public function __construct(
        public readonly int $categoryId,
        public readonly string $name,
    ) {}

    public function addBook(Book $book): void
    {
        $this->books[$book->id] = $book;
    }

    public function removeBook(int $id): void
    {
        unset($this->books[$id]);
    }

    public function searchBook(string $title): ?Book
    {
        foreach ($this->books as $book) {
            if (stripos($book->title, $title) !== false) {
                return $book;
            }
        }

        return null;
    }

    public function getBooks(): array
    {
        return array_values($this->books);
    }
}
