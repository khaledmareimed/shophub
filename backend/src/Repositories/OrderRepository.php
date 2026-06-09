<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class OrderRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function insertOrder(array $o): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO orders (code, customer_id, subtotal, shipping_fee, discount_total, grand_total, payment_method, payment_status, status, shipping_address_json, customer_notes, coupon_code)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $st->execute([
            $o['code'],
            $o['customer_id'],
            $o['subtotal'],
            $o['shipping_fee'],
            $o['discount_total'],
            $o['grand_total'],
            $o['payment_method'],
            $o['payment_status'],
            $o['status'],
            $o['shipping_address_json'],
            $o['customer_notes'] ?? null,
            $o['coupon_code'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function insertItem(array $i): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, seller_id, name_snapshot, image_path_snapshot, price_snapshot, qty, line_total, fulfillment_status)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $i['order_id'],
            $i['product_id'],
            $i['seller_id'],
            $i['name_snapshot'],
            $i['image_path_snapshot'],
            $i['price_snapshot'],
            $i['qty'],
            $i['line_total'],
            $i['fulfillment_status'] ?? 'pending',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findByCode(string $code): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM orders WHERE code = ?');
        $st->execute([$code]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function items(int $orderId): array
    {
        $st = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $st->execute([$orderId]);
        return $st->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function listCustomer(int $userId, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $st = $this->pdo->prepare(
            'SELECT SQL_CALC_FOUND_ROWS * FROM orders WHERE customer_id = ? ORDER BY placed_at DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off
        );
        $st->execute([$userId]);
        $rows = $st->fetchAll();
        $total = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    /** @return list<array<string, mixed>> */
    public function listAdmin(?string $status, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $params = [];
        $where = '1=1';
        if ($status !== null && $status !== 'all') {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM orders WHERE ' . $where
            . ' ORDER BY placed_at DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return [$st->fetchAll(), (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    /** @return list<array<string, mixed>> */
    public function listSellerLines(int $sellerId, ?string $fulfillment, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $params = [$sellerId];
        $where = 'oi.seller_id = ?';
        if ($fulfillment !== null && $fulfillment !== 'all') {
            $where .= ' AND oi.fulfillment_status = ?';
            $params[] = $fulfillment;
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS oi.*, o.code order_code, o.status order_status, o.placed_at
                FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE ' . $where
            . ' ORDER BY o.placed_at DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return [$st->fetchAll(), (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
    }

    public function cancelOrder(int $orderId, string $reason): void
    {
        $this->pdo->prepare(
            'UPDATE orders SET status = "cancelled", cancelled_at = UTC_TIMESTAMP(), cancel_reason = ? WHERE id = ?'
        )->execute([$reason, $orderId]);
    }

    public function markPaidIfCompleted(int $orderId): void
    {
        $this->pdo->prepare(
            'UPDATE orders SET payment_status = "paid" WHERE id = ? AND status = "completed"'
        )->execute([$orderId]);
    }

    public function completeOrder(int $orderId): void
    {
        $this->pdo->prepare(
            'UPDATE orders SET status = "completed", completed_at = UTC_TIMESTAMP() WHERE id = ?'
        )->execute([$orderId]);
    }

    public function updateItemFulfillment(int $itemId, string $status): void
    {
        $this->pdo->prepare('UPDATE order_items SET fulfillment_status = ? WHERE id = ?')
            ->execute([$status, $itemId]);
    }

    /** @return array<string, mixed>|null */
    public function findItem(int $itemId): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM order_items WHERE id = ?');
        $st->execute([$itemId]);
        $r = $st->fetch();
        return $r ?: null;
    }
}
