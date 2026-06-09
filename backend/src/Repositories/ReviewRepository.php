<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ReviewRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function insert(array $r): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO reviews (product_id, customer_id, order_item_id, rating, title, body, status)
             VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([
            $r['product_id'],
            $r['customer_id'],
            $r['order_item_id'],
            $r['rating'],
            $r['title'] ?? null,
            $r['body'] ?? null,
            $r['status'] ?? 'pending',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string, mixed>> */
    public function approvedForProduct(int $productId): array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM reviews WHERE product_id = ? AND status = "approved" ORDER BY id DESC'
        );
        $st->execute([$productId]);
        return $st->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findByOrderItem(int $orderItemId): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM reviews WHERE order_item_id = ?');
        $st->execute([$orderItemId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function setStatus(int $id, string $status): void
    {
        $this->pdo->prepare('UPDATE reviews SET status = ? WHERE id = ?')->execute([$status, $id]);
    }
}
