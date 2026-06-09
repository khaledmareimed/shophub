<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Services\CartService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/cart/cart.php');
}
$user = require_role('customer');

$itemId = (int) ($_POST['item_id'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));

try {
    app(CartService::class)->updateQty((int) $user['id'], $itemId, $qty);
    flash('success', 'Cart updated.');
} catch (\Throwable $e) {
    flash('error', 'Could not update item.');
}
redirect('/store/pages/cart/cart.php');
