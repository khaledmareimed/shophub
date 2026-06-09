<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/products/products-list.php');
}
$user = require_role('seller');

$id = (int) ($_POST['id'] ?? 0);
$repo = app(ProductRepository::class);
$p = $id > 0 ? $repo->findById($id) : null;
if (!$p || (int) $p['seller_id'] !== (int) $user['id']) {
    flash('error', 'Product not found.');
    redirect('/seller/pages/products/products-list.php');
}

$repo->softDelete($id);
flash('success', 'Product deleted.');
redirect('/seller/pages/products/products-list.php');
