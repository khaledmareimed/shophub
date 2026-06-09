<?php

declare(strict_types=1);

namespace App\Web;

final class I18n
{
    private const SUPPORTED = ['en', 'ar'];
    private const DEFAULT = 'en';

    private static ?string $locale = null;

    /** @var array<string, array<string, string>> */
    private static array $cache = [];

    public static function resolve(?array $user = null): string
    {
        if (self::$locale !== null) {
            return self::$locale;
        }
        $candidates = [
            $_GET['lang'] ?? null,
            $_SESSION['lang'] ?? null,
            $user['locale'] ?? null,
            self::fromAcceptLanguage(),
            self::DEFAULT,
        ];
        foreach ($candidates as $c) {
            $c = is_string($c) ? strtolower($c) : null;
            if ($c !== null && in_array($c, self::SUPPORTED, true)) {
                self::$locale = $c;
                $_SESSION['lang'] = $c;
                return $c;
            }
        }
        self::$locale = self::DEFAULT;
        return self::DEFAULT;
    }

    public static function locale(): string
    {
        return self::$locale ?? self::DEFAULT;
    }

    public static function dir(): string
    {
        return self::locale() === 'ar' ? 'rtl' : 'ltr';
    }

    public static function isRtl(): bool
    {
        return self::locale() === 'ar';
    }

    public static function t(string $key, array $params = []): string
    {
        $loc = self::locale();
        if (!isset(self::$cache[$loc])) {
            $path = dirname(__DIR__) . '/i18n/' . $loc . '.php';
            self::$cache[$loc] = is_file($path) ? (array) require $path : [];
        }
        $out = self::$cache[$loc][$key] ?? $key;
        if ($params !== []) {
            foreach ($params as $k => $v) {
                $out = str_replace('{' . $k . '}', (string) $v, $out);
            }
        }
        return $out;
    }

    private static function fromAcceptLanguage(): ?string
    {
        $hdr = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (!is_string($hdr) || $hdr === '') {
            return null;
        }
        $first = strtolower(trim(explode(',', $hdr)[0] ?? ''));
        $two = substr($first, 0, 2);
        return in_array($two, self::SUPPORTED, true) ? $two : null;
    }
}
