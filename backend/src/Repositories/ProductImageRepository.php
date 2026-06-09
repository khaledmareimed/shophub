<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ProductImageRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return list<array<string, mixed>> */
    public function byProduct(int $productId): array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY position ASC, id ASC'
        );
        $st->execute([$productId]);
        return $st->fetchAll();
    }

    public function insert(int $productId, string $path, ?string $alt, int $position, bool $primary): int
    {
        if ($primary) {
            $this->pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?')
                ->execute([$productId]);
        }
        $st = $this->pdo->prepare(
            'INSERT INTO product_images (product_id, path, alt, position, is_primary) VALUES (?,?,?,?,?)'
        );
        $st->execute([$productId, $path, $alt, $position, $primary ? 1 : 0]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $imageId, int $productId): void
    {
        $st = $this->pdo->prepare('DELETE FROM product_images WHERE id = ? AND product_id = ?');
        $st->execute([$imageId, $productId]);
    }
}
