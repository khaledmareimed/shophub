<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CartRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function getOrCreateCartId(int $userId): int
    {
        $st = $this->pdo->prepare('SELECT id FROM carts WHERE user_id = ?');
        $st->execute([$userId]);
        $r = $st->fetch();
        if ($r) {
            return (int) $r['id'];
        }
        $this->pdo->prepare('INSERT INTO carts (user_id) VALUES (?)')->execute([$userId]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string, mixed>> */
    public function itemsWithProduct(int $cartId): array
    {
        $sql = 'SELECT ci.*, p.name, p.slug, p.status FROM cart_items ci
                JOIN products p ON p.id = ci.product_id
                WHERE ci.cart_id = ?';
        $st = $this->pdo->prepare($sql);
        $st->execute([$cartId]);
        return $st->fetchAll();
    }

    public function upsertItem(int $cartId, int $productId, int $qty, string $price): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO cart_items (cart_id, product_id, qty, price_snapshot) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), price_snapshot = VALUES(price_snapshot)'
        );
        $st->execute([$cartId, $productId, $qty, $price]);
    }

    public function setQty(int $cartItemId, int $qty): void
    {
        $this->pdo->prepare('UPDATE cart_items SET qty = ? WHERE id = ?')->execute([$qty, $cartItemId]);
    }

    public function deleteItem(int $cartItemId, int $cartId): void
    {
        $this->pdo->prepare('DELETE FROM cart_items WHERE id = ? AND cart_id = ?')->execute([$cartItemId, $cartId]);
    }

    public function clear(int $cartId): void
    {
        $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
    }
}
