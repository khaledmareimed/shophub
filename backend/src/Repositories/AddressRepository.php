<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AddressRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return list<array<string, mixed>> */
    public function listForUser(int $userId): array
    {
        $st = $this->pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id, int $userId): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ?');
        $st->execute([$id, $userId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @param array<string, mixed> $a */
    public function insert(int $userId, array $a): int
    {
        if (!empty($a['is_default'])) {
            $this->pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$userId]);
        }
        $st = $this->pdo->prepare(
            'INSERT INTO addresses (user_id, label, recipient_name, phone, line1, line2, city, country, postal_code, is_default)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $userId,
            $a['label'] ?? null,
            $a['recipient_name'],
            $a['phone'] ?? null,
            $a['line1'],
            $a['line2'] ?? null,
            $a['city'],
            $a['country'] ?? 'JO',
            $a['postal_code'] ?? null,
            !empty($a['is_default']) ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $a */
    public function update(int $id, int $userId, array $a): void
    {
        if (!empty($a['is_default'])) {
            $this->pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$userId]);
        }
        $st = $this->pdo->prepare(
            'UPDATE addresses SET label=?, recipient_name=?, phone=?, line1=?, line2=?, city=?, country=?, postal_code=?, is_default=? WHERE id=? AND user_id=?'
        );
        $st->execute([
            $a['label'] ?? null,
            $a['recipient_name'],
            $a['phone'] ?? null,
            $a['line1'],
            $a['line2'] ?? null,
            $a['city'],
            $a['country'] ?? 'JO',
            $a['postal_code'] ?? null,
            !empty($a['is_default']) ? 1 : 0,
            $id,
            $userId,
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $this->pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
    }
}
