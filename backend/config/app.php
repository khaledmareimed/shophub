<?php

declare(strict_types=1);

return [
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => 'UTC',
    'mailbox_dir' => dirname(__DIR__) . '/' . ($_ENV['MAILBOX_DIR'] ?? 'storage/mailbox'),
    'log_dir' => dirname(__DIR__) . '/' . ($_ENV['LOG_DIR'] ?? 'storage/logs'),
    'upload_products_dir' => dirname(__DIR__) . '/public/uploads/products',
];
