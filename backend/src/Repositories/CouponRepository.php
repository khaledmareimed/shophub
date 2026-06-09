<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CouponRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed>|null */
    public function findByCode(string $code): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM coupons WHERE code = ? AND active = 1');
        $st->execute([strtoupper(trim($code))]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM coupons WHERE id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM coupons WHERE id = ?')->execute([$id]);
    }

    /** @param array<string, mixed> $d */
    public function insert(array $d): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO coupons (code, type, value, min_subtotal, max_discount, starts_at, expires_at, usage_limit, scope, scope_id, active)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            strtoupper($d['code']),
            $d['type'],
            $d['value'],
            $d['min_subtotal'] ?? null,
            $d['max_discount'] ?? null,
            $d['starts_at'] ?? null,
            $d['expires_at'] ?? null,
            $d['usage_limit'] ?? null,
            $d['scope'] ?? 'all',
            $d['scope_id'] ?? null,
            $d['active'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $d */
    public function update(int $id, array $d): void
    {
        $st = $this->pdo->prepare(
            'UPDATE coupons SET code=?, type=?, value=?, min_subtotal=?, max_discount=?, starts_at=?, expires_at=?, usage_limit=?, scope=?, scope_id=?, active=? WHERE id=?'
        );
        $st->execute([
            strtoupper($d['code']),
            $d['type'],
            $d['value'],
            $d['min_subtotal'] ?? null,
            $d['max_discount'] ?? null,
            $d['starts_at'] ?? null,
            $d['expires_at'] ?? null,
            $d['usage_limit'] ?? null,
            $d['scope'] ?? 'all',
            $d['scope_id'] ?? null,
            $d['active'] ?? 1,
            $id,
        ]);
    }

    public function incrementUsed(int $id): void
    {
        $this->pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$id]);
    }

    public function insertRedemption(int $couponId, int $orderId, int $customerId, string $amount): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO coupon_redemptions (coupon_id, order_id, customer_id, amount) VALUES (?,?,?,?)'
        );
        $st->execute([$couponId, $orderId, $customerId, $amount]);
    }
}
