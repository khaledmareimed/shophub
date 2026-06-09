<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SettingsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed> */
    public function get(string $key, array $default = []): array
    {
        $st = $this->pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
        $st->execute([$key]);
        $row = $st->fetch();
        if (!$row) {
            return $default;
        }
        $v = json_decode((string) $row['value'], true);
        return is_array($v) ? $v : $default;
    }

    /** @param array<string, mixed> $value */
    public function set(string $key, array $value): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO settings (`key`, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $st->execute([$key, json_encode($value)]);
    }
}
