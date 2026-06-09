<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Services\CartService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/cart/cart.php');
}
$user = require_role('customer');

$itemId = (int) ($_POST['item_id'] ?? 0);
try {
    app(CartService::class)->remove((int) $user['id'], $itemId);
    flash('success', 'Item removed from cart.');
} catch (\Throwable $e) {
    flash('error', 'Could not remove item.');
}
redirect('/store/pages/cart/cart.php');
