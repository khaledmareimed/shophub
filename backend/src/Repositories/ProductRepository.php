<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM products WHERE id = ? AND deleted_at IS NULL');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM products WHERE slug = ? AND deleted_at IS NULL');
        $st->execute([$slug]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function incrementView(int $id): void
    {
        $this->pdo->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?')->execute([$id]);
    }

    /** @param array<string, mixed> $d */
    public function insert(array $d): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO products (seller_id, category_id, slug, name, name_ar, description, description_ar, price, compare_at_price, sku, stock, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $d['seller_id'],
            $d['category_id'],
            $d['slug'],
            $d['name'],
            $d['name_ar'] ?? null,
            $d['description'] ?? null,
            $d['description_ar'] ?? null,
            $d['price'],
            $d['compare_at_price'] ?? null,
            $d['sku'] ?? null,
            $d['stock'] ?? 0,
            $d['status'] ?? 'draft',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $d */
    public function update(int $id, array $d): void
    {
        $st = $this->pdo->prepare(
            'UPDATE products SET category_id = ?, slug = ?, name = ?, name_ar = ?, description = ?, description_ar = ?,
             price = ?, compare_at_price = ?, sku = ?, stock = ?, status = ?, rejection_reason = ?
             WHERE id = ? AND deleted_at IS NULL'
        );
        $st->execute([
            $d['category_id'],
            $d['slug'],
            $d['name'],
            $d['name_ar'] ?? null,
            $d['description'] ?? null,
            $d['description_ar'] ?? null,
            $d['price'],
            $d['compare_at_price'] ?? null,
            $d['sku'] ?? null,
            $d['stock'],
            $d['status'],
            $d['rejection_reason'] ?? null,
            $id,
        ]);
    }

    public function setStatus(int $id, string $status, ?string $reason): void
    {
        $st = $this->pdo->prepare('UPDATE products SET status = ?, rejection_reason = ? WHERE id = ?');
        $st->execute([$status, $reason, $id]);
    }

    public function softDelete(int $id): void
    {
        $this->pdo->prepare('UPDATE products SET deleted_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$id]);
    }

    /**
     * @return array{0: list<array<string,mixed>>, 1: int}
     */
    public function searchStores(
        ?string $q,
        ?int $categoryId,
        string $status,
        int $page,
        int $per,
        string $sort,
    ): array {
        $off = max(0, ($page - 1) * $per);
        $where = ['p.deleted_at IS NULL', 'p.status = ?'];
        $params = [$status];
        if ($categoryId !== null) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($q !== null && $q !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $order = match ($sort) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest' => 'p.id DESC',
            default => 'p.id DESC',
        };
        $sql = 'SELECT SQL_CALC_FOUND_ROWS p.* FROM products p WHERE ' . implode(' AND ', $where)
            . ' ORDER BY ' . $order . ' LIMIT ' . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        $total = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    /**
     * @return array{0: list<array<string,mixed>>, 1: int}
     */
    public function searchSeller(int $sellerId, ?string $status, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $where = 'p.seller_id = ? AND p.deleted_at IS NULL';
        $params = [$sellerId];
        if ($status !== null && $status !== 'all') {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS p.* FROM products p WHERE ' . $where
            . ' ORDER BY p.id DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return [$st->fetchAll(), (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    /**
     * @return array{0: list<array<string,mixed>>, 1: int}
     */
    public function searchAdmin(?string $status, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $params = [];
        $where = 'p.deleted_at IS NULL';
        if ($status !== null && $status !== 'all') {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS p.* FROM products p WHERE ' . $where
            . ' ORDER BY p.id DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return [$st->fetchAll(), (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function lockRowForStock(int $id): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, stock, status FROM products WHERE id = ? AND deleted_at IS NULL FOR UPDATE'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function adjustStock(int $id, int $delta): void
    {
        $this->pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$delta, $id]);
    }

    public function bumpSold(int $id, int $qty): void
    {
        $this->pdo->prepare('UPDATE products SET sold_count = sold_count + ? WHERE id = ?')->execute([$qty, $id]);
    }

    public function updateRatingAggregate(int $productId): void
    {
        $st = $this->pdo->prepare(
            'SELECT AVG(rating) a, COUNT(*) c FROM reviews WHERE product_id = ? AND status = "approved"'
        );
        $st->execute([$productId]);
        $row = $st->fetch();
        $avg = round((float) ($row['a'] ?? 0), 2);
        $c = (int) ($row['c'] ?? 0);
        $this->pdo->prepare('UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?')
            ->execute([$avg, $c, $productId]);
    }
}
