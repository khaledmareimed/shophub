<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/autoload.php';

use App\Core\Env;
use App\Services\MigrationRunner;

Env::load($root . '/.env');

$dbCfg = require $root . '/config/database.php';
$runner = new MigrationRunner($root . '/database/migrations', $dbCfg);

foreach ($runner->run() as $row) {
    echo "{$row['status']} {$row['filename']}\n";
}

echo "migrations done.\n";
