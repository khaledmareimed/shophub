<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class WishlistRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return list<array<string, mixed>> */
    public function list(int $userId): array
    {
        $sql = 'SELECT w.*, p.name, p.slug, p.price, p.status FROM wishlists w
                JOIN products p ON p.id = w.product_id
                WHERE w.user_id = ? ORDER BY w.id DESC';
        $st = $this->pdo->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function add(int $userId, int $productId): void
    {
        $st = $this->pdo->prepare(
            'INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?,?)'
        );
        $st->execute([$userId, $productId]);
    }

    public function remove(int $userId, int $productId): void
    {
        $this->pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?')
            ->execute([$userId, $productId]);
    }
}
