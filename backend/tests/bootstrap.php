<?php

declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root . '/vendor/autoload.php')) {
    fwrite(STDERR, "Install dev dependencies: cd backend && composer install\n");
    exit(1);
}

require $root . '/vendor/autoload.php';
