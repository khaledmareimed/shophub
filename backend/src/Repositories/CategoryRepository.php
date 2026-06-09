<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CategoryRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $st->execute([$slug]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function allActive(): array
    {
        return $this->pdo->query(
            'SELECT * FROM categories WHERE active = 1 ORDER BY position ASC, id ASC'
        )->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function allAdmin(): array
    {
        return $this->pdo->query('SELECT * FROM categories ORDER BY position ASC, id ASC')->fetchAll();
    }

    /** @param array<string, mixed> $d */
    public function insert(array $d): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO categories (parent_id, slug, name, name_ar, description, image_path, position, active)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $d['parent_id'] ?? null,
            $d['slug'],
            $d['name'],
            $d['name_ar'] ?? null,
            $d['description'] ?? null,
            $d['image_path'] ?? null,
            $d['position'] ?? 0,
            $d['active'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $d */
    public function update(int $id, array $d): void
    {
        $st = $this->pdo->prepare(
            'UPDATE categories SET parent_id = ?, slug = ?, name = ?, name_ar = ?, description = ?, image_path = ?, position = ?, active = ?
             WHERE id = ?'
        );
        $st->execute([
            $d['parent_id'] ?? null,
            $d['slug'],
            $d['name'],
            $d['name_ar'] ?? null,
            $d['description'] ?? null,
            $d['image_path'] ?? null,
            $d['position'] ?? 0,
            $d['active'] ?? 1,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        $st->execute([$id]);
    }
}
