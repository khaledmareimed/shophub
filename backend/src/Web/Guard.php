<?php

declare(strict_types=1);

namespace App\Web;

use App\Repositories\UserRepository;

final class Guard
{
    /** @return array<string, mixed>|null */
    public static function currentUser(): ?array
    {
        $uid = $_SESSION['uid'] ?? null;
        if (!is_int($uid) && !ctype_digit((string) $uid)) {
            return null;
        }
        $user = (new UserRepository())->findById((int) $uid);
        if (!$user || $user['status'] === 'banned') {
            Session::destroy();
            return null;
        }
        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['uid'] = (int) $user['id'];
        $_SESSION['role'] = (string) $user['role'];
        $_SESSION['lang'] = (string) ($user['locale'] ?? 'en');
    }

    public static function logout(): void
    {
        Session::destroy();
        Session::start();
    }

    /**
     * Redirects to the matching login page when not authenticated or the
     * authenticated role does not match.
     */
    public static function require(string $role): array
    {
        $user = self::currentUser();
        if ($user === null) {
            self::redirectToLogin($role);
        }
        if ($user['role'] !== $role) {
            self::redirectToLogin($role);
        }
        return $user;
    }

    public static function loginPathFor(string $role): string
    {
        return match ($role) {
            'admin' => '/admin/pages/auth/login.php',
            'seller' => '/seller/pages/auth/login.php',
            default => '/store/pages/auth/login.php',
        };
    }

    public static function homePathFor(string $role): string
    {
        return match ($role) {
            'admin' => '/admin/index.php',
            'seller' => '/seller/index.php',
            default => '/store/index.php',
        };
    }

    private static function redirectToLogin(string $role): never
    {
        $next = $_SERVER['REQUEST_URI'] ?? '/';
        $url = self::loginPathFor($role) . '?next=' . urlencode($next);
        header('Location: ' . $url, true, 302);
        exit;
    }
}
