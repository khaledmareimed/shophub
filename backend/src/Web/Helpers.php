<?php

declare(strict_types=1);

use App\Web\Flash;
use App\Web\Guard;
use App\Web\I18n;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app')) {
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    function app(string $class): object
    {
        return App\Web\Container::get($class);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }
}

if (!function_exists('back')) {
    function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (is_string($ref) && $ref !== '' && parse_url($ref, PHP_URL_HOST) === $host) {
            redirect($ref);
        }
        redirect($fallback);
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        Flash::add($type, $message);
    }
}

if (!function_exists('flash_pull')) {
    /** @return list<array{type:string,message:string}> */
    function flash_pull(): array
    {
        return Flash::pull();
    }
}

if (!function_exists('old_input')) {
    /** @return array<string, mixed> */
    function old_input(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Flash::pullInput();
        }
        return $cache;
    }
}

if (!function_exists('old')) {
    function old(string $field, mixed $default = ''): mixed
    {
        $vals = old_input();
        return array_key_exists($field, $vals) ? $vals[$field] : $default;
    }
}

if (!function_exists('errors_pull')) {
    /** @return array<string, string> */
    function errors_pull(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Flash::pullErrors();
        }
        return $cache;
    }
}

if (!function_exists('error_for')) {
    function error_for(string $field): ?string
    {
        $errs = errors_pull();
        return $errs[$field] ?? null;
    }
}

if (!function_exists('current_user')) {
    /** @return array<string, mixed>|null */
    function current_user(): ?array
    {
        static $cache = false;
        if ($cache === false) {
            $cache = Guard::currentUser();
        }
        return $cache;
    }
}

if (!function_exists('require_role')) {
    /** @return array<string, mixed> */
    function require_role(string $role): array
    {
        return Guard::require($role);
    }
}

if (!function_exists('require_login')) {
    /** @return array<string, mixed> */
    function require_login(): array
    {
        $u = current_user();
        if ($u === null) {
            $next = $_SERVER['REQUEST_URI'] ?? '/';
            redirect('/store/pages/auth/login.php?next=' . urlencode($next));
        }
        return $u;
    }
}

if (!function_exists('lang')) {
    function lang(): string
    {
        return I18n::locale();
    }
}

if (!function_exists('dir_attr')) {
    function dir_attr(): string
    {
        return I18n::dir();
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return I18n::isRtl();
    }
}

if (!function_exists('t')) {
    function t(string $key, array $params = []): string
    {
        return I18n::t($key, $params);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('upload_url')) {
    function upload_url(?string $relative): string
    {
        if (!is_string($relative) || $relative === '') {
            return '/store/assets/placeholder.svg';
        }
        $clean = ltrim($relative, '/');
        if (!str_starts_with($clean, 'uploads/')) {
            $clean = 'uploads/' . $clean;
        }
        return '/' . $clean;
    }
}

if (!function_exists('format_money')) {
    function format_money(string|float|int $value, string $currency = 'USD'): string
    {
        $v = is_string($value) ? $value : (string) $value;
        return '$' . number_format((float) $v, 2);
    }
}

if (!function_exists('localized')) {
    /**
     * Return the locale-appropriate value for a row that has both `name` and `name_ar` style columns.
     *
     * @param array<string, mixed> $row
     */
    function localized(array $row, string $base = 'name'): string
    {
        $arKey = $base . '_ar';
        if (lang() === 'ar' && !empty($row[$arKey])) {
            return (string) $row[$arKey];
        }
        return (string) ($row[$base] ?? '');
    }
}

if (!function_exists('product_image_url')) {
    /**
     * @param array<string,mixed>|null $product full product row, or null
     * @return string usable <img src> URL
     */
    function product_image_url(?array $product = null, ?int $productId = null): string
    {
        $pid = $product['id'] ?? $productId;
        if (!$pid) {
            return '/store/assets/images/placeholder.svg';
        }
        $imgs = app(\App\Repositories\ProductImageRepository::class)->byProduct((int) $pid);
        if ($imgs === []) {
            return '/store/assets/images/placeholder.svg';
        }
        return upload_url((string) $imgs[0]['path']);
    }
}

if (!function_exists('star_row')) {
    function star_row(string|float $rating, int $count = 0): string
    {
        $r = (float) $rating;
        $full = (int) floor($r);
        $half = ($r - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        $stars = str_repeat('★', $full) . ($half ? '☆' : '') . str_repeat('☆', $empty);
        $countHtml = $count > 0 ? '<span class="rating-count">(' . e((string) $count) . ')</span>' : '';
        return '<span class="stars">' . e($stars) . '</span>' . $countHtml;
    }
}

if (!function_exists('current_url')) {
    function current_url(): string
    {
        return (string) ($_SERVER['REQUEST_URI'] ?? '/');
    }
}
