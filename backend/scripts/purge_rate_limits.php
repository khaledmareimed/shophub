<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/autoload.php';

use App\Core\Database;
use App\Core\Env;

Env::load($root . '/.env');
Database::init(require $root . '/config/database.php');
$pdo = Database::pdo();
$since = gmdate('Y-m-d H:i:s', time() - 7200);
$st = $pdo->prepare('DELETE FROM rate_limits WHERE window_start < ?');
$st->execute([$since]);
echo "purged old rate limits\n";
