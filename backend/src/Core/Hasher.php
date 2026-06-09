<?php

declare(strict_types=1);

namespace App\Core;

final class Hasher
{
    public static function hashPassword(string $plain, string $pepper = ''): string
    {
        return password_hash($pepper . $plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $plain, string $hash, string $pepper = ''): bool
    {
        return password_verify($pepper . $plain, $hash);
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function sha256Hex(string $s): string
    {
        return hash('sha256', $s);
    }
}
