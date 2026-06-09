<?php

declare(strict_types=1);

namespace App\Web;

final class Session
{
    public static function start(): void
    {
        if (PHP_SESSION_ACTIVE === session_status()) {
            return;
        }

        $sessionDir = dirname(__DIR__, 2) . '/storage/sessions';
        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0775, true);
        }
        if (is_dir($sessionDir) && is_writable($sessionDir)) {
            session_save_path($sessionDir);
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => trim((string) ($_ENV['SESSION_DOMAIN'] ?? '')),
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('SHOPHUBSESSID');
        session_start();
    }

    public static function regenerate(): void
    {
        if (PHP_SESSION_ACTIVE === session_status()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (PHP_SESSION_ACTIVE !== session_status()) {
            self::start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }
        session_destroy();
    }
}
