<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function init(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function log(string $dir, string $level, string $message, array $ctx = []): void
    {
        self::init($dir);
        $file = $dir . '/app-' . gmdate('Y-m-d') . '.log';
        $line = gmdate('c') . " [$level] $message " . ($ctx !== [] ? json_encode($ctx) : '') . "\n";
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
