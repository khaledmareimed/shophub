<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\WishlistRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/index.php');
}

$next = (string) ($_POST['next'] ?? '/store/pages/account/wishlist.php');
if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
    $next = '/store/pages/account/wishlist.php';
}

$user = current_user();
if ($user === null || $user['role'] !== 'customer') {
    flash('error', 'Please sign in to use your wishlist.');
    redirect('/store/pages/auth/login.php?next=' . urlencode($next));
}

$productId = (int) ($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    flash('error', 'Invalid product.');
    redirect($next);
}

$repo = app(WishlistRepository::class);
$existing = $repo->list((int) $user['id']);
$has = false;
foreach ($existing as $w) {
    if ((int) $w['product_id'] === $productId) {
        $has = true;
        break;
    }
}
if ($has) {
    $repo->remove((int) $user['id'], $productId);
    flash('success', 'Removed from wishlist.');
} else {
    $repo->add((int) $user['id'], $productId);
    flash('success', 'Added to wishlist.');
}
redirect($next);
