<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/products/products-list.php');
}
$user = require_role('seller');

$productId = (int) ($_POST['product_id'] ?? 0);
$imageId = (int) ($_POST['image_id'] ?? 0);

$product = $productId > 0 ? app(ProductRepository::class)->findById($productId) : null;
if (!$product || (int) $product['seller_id'] !== (int) $user['id']) {
    flash('error', 'Product not found.');
    redirect('/seller/pages/products/products-list.php');
}

app(ProductImageRepository::class)->delete($imageId, $productId);
flash('success', 'Image removed.');
redirect('/seller/pages/products/product-edit.php?id=' . $productId);
