<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Services\CartService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/cart/cart.php');
}
$user = require_role('customer');

app(CartService::class)->clear((int) $user['id']);
flash('success', 'Cart cleared.');
redirect('/store/pages/cart/cart.php');
