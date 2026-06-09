<?php

declare(strict_types=1);

namespace App\Web;

final class Flash
{
    private const KEY = '_flash';

    public static function add(string $type, string $message): void
    {
        $_SESSION[self::KEY][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type:string,message:string}> */
    public static function pull(): array
    {
        $items = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return is_array($items) ? array_values($items) : [];
    }

    public static function keepInput(array $input): void
    {
        $_SESSION['_old'] = $input;
    }

    /** @return array<string, mixed> */
    public static function pullInput(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
        return is_array($old) ? $old : [];
    }

    public static function keepErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }

    /** @return array<string, string> */
    public static function pullErrors(): array
    {
        $errs = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return is_array($errs) ? $errs : [];
    }
}
