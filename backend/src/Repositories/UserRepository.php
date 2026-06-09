<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, email, password_hash, name, phone, locale, role, status, email_verified_at, created_at
             FROM users WHERE id = ? AND deleted_at IS NULL'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, email, password_hash, name, phone, locale, role, status FROM users
             WHERE email = ? AND deleted_at IS NULL'
        );
        $st->execute([strtolower(trim($email))]);
        $r = $st->fetch();
        return $r ?: null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, name, phone, locale, role, status)
             VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([
            strtolower(trim((string) $data['email'])),
            $data['password_hash'],
            $data['name'],
            $data['phone'] ?? null,
            $data['locale'] ?? 'en',
            $data['role'],
            $data['status'] ?? 'active',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $st = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public function updateLocale(int $id, string $locale): void
    {
        $st = $this->pdo->prepare('UPDATE users SET locale = ? WHERE id = ?');
        $st->execute([$locale, $id]);
    }

    /** @return list<array<string, mixed>> */
    public function listPaginated(string $roleFilter, ?string $status, int $page, int $perPage): array
    {
        $off = max(0, ($page - 1) * $perPage);
        $where = ['1=1'];
        $params = [];
        if ($roleFilter !== 'all') {
            $where[] = 'role = ?';
            $params[] = $roleFilter;
        }
        if ($status !== null && $status !== 'all') {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS id, email, name, phone, locale, role, status, created_at
                FROM users WHERE deleted_at IS NULL AND ' . implode(' AND ', $where)
            . ' ORDER BY id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        $total = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }
}
