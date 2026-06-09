<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Services\DatabaseSeeder;

Env::load($root . '/.env');

Database::init(require $root . '/config/database.php');

$seed = (new DatabaseSeeder(
    (string) ($_ENV['AUTH_PEPPER'] ?? $_ENV['JWT_PEPPER'] ?? ''),
))->run();

if (!$seed['ok']) {
    fwrite(STDERR, "seed failed: {$seed['message']}\n");
    exit(1);
}

foreach ($seed['credentials'] as $role => $cred) {
    echo "{$role}: {$cred['email']} / {$cred['password']}\n";
}
echo "seed ok.\n";
