<?php

declare(strict_types=1);

/**
 * One-shot bootstrap loaded at the top of every public PHP page.
 *
 * Resolution chain:
 *   - PSR-4 autoload (Composer + project fallback)
 *   - .env from backend/.env (or .env.example as fallback for first-run)
 *   - PDO connection
 *   - Session (SameSite=Lax, HttpOnly, Secure when HTTPS)
 *   - Locale resolution (?lang → session → users.locale → Accept-Language → en)
 *   - Helper functions (e, redirect, flash, t, current_user, ...)
 *   - Security headers
 */

use App\Core\Database;
use App\Core\Env;
use App\Core\Logger;
use App\Web\Guard;
use App\Web\I18n;
use App\Web\Session;

$projectRoot = __DIR__;
$backendRoot = $projectRoot . '/backend';

require $backendRoot . '/autoload.php';
require $backendRoot . '/src/Web/Helpers.php';

Env::load($backendRoot . '/.env');
if (!is_file($backendRoot . '/.env')) {
    Env::load($backendRoot . '/.env.example');
}

date_default_timezone_set('UTC');

$logDir = $_ENV['LOG_DIR'] ?? 'storage/logs';
$absLogDir = str_starts_with($logDir, '/') ? $logDir : $backendRoot . '/' . $logDir;
Logger::init($absLogDir);

$debug = filter_var($_ENV['APP_DEBUG'] ?? '0', FILTER_VALIDATE_BOOLEAN);
set_error_handler(static function (int $no, string $msg, string $file, int $line) use ($absLogDir): bool {
    if (!(error_reporting() & $no)) {
        return false;
    }
    Logger::log($absLogDir, 'ERROR', $msg, ['file' => $file, 'line' => $line]);
    throw new \ErrorException($msg, 0, $no, $file, $line);
});
set_exception_handler(static function (\Throwable $e) use ($absLogDir, $debug): void {
    Logger::log($absLogDir, 'EXCEPTION', $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Server error: {$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}\n\n{$e->getTraceAsString()}";
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><title>Server error</title>'
            . '<h1 style="font-family:system-ui">Something went wrong.</h1>'
            . '<p style="font-family:system-ui">Please try again in a moment.</p>';
    }
    exit;
});

Database::init(require $backendRoot . '/config/database.php');

Session::start();

$currentUser = Guard::currentUser();
$lang = I18n::resolve($currentUser);

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

return [
    'user' => $currentUser,
    'lang' => $lang,
];
