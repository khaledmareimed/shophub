<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Services\CartService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/index.php');
}

$next = (string) ($_POST['next'] ?? '/store/pages/cart/cart.php');
if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
    $next = '/store/pages/cart/cart.php';
}

$user = current_user();
if ($user === null || $user['role'] !== 'customer') {
    flash('error', 'Please sign in to add items to your cart.');
    redirect('/store/pages/auth/login.php?next=' . urlencode($next));
}

$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));
if ($productId <= 0) {
    flash('error', 'Invalid product.');
    redirect($next);
}

try {
    app(CartService::class)->add((int) $user['id'], $productId, $qty);
    flash('success', 'Added to cart.');
} catch (\InvalidArgumentException $e) {
    flash('error', 'This product is no longer available.');
} catch (\Throwable $e) {
    flash('error', 'Could not add to cart.');
}
redirect($next);
