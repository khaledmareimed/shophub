<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PasswordResetRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(int $userId, string $hash, string $expiresAt): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)'
        );
        $st->execute([$userId, $hash, $expiresAt]);
    }

    /** @return array<string, mixed>|null */
    public function findValid(string $hash): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() ORDER BY id DESC LIMIT 1'
        );
        $st->execute([$hash]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function markUsed(int $id): void
    {
        $st = $this->pdo->prepare('UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE id = ?');
        $st->execute([$id]);
    }
}
