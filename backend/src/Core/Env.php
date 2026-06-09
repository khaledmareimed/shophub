<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    /** @param array<string, mixed> $overrides */
    public static function load(string $path, array $overrides = []): void
    {
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\"'");
            if ($k !== '') {
                $_ENV[$k] = $v;
                putenv($k . '=' . $v);
            }
        }
        foreach ($overrides as $k => $v) {
            $_ENV[(string) $k] = $v;
            putenv((string) $k . '=' . (string) $v);
        }
    }
}
