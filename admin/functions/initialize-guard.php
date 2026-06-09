<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\MigrationRunner;

/** @param array{host:string,port:int,database:string,username:string,password:string,charset:string} $dbCfg */
function initialize_is_allowed(array $dbCfg): bool
{
    $debug = filter_var($_ENV['APP_DEBUG'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    if ($debug) {
        return true;
    }

    $runner = new MigrationRunner(
        dirname(__DIR__, 2) . '/backend/database/migrations',
        $dbCfg,
    );

    if ($runner->testConnection() !== null) {
        return true;
    }

    try {
        Database::init($dbCfg);
        $pdo = Database::pdo();
        $st = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND deleted_at IS NULL");

        return (int) $st->fetchColumn() === 0;
    } catch (Throwable) {
        return true;
    }
}
