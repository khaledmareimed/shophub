<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CategoryRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;

$user = require_role('seller');

$id = (int) ($_GET['id'] ?? 0);
$product = $id > 0 ? app(ProductRepository::class)->findById($id) : null;
if (!$product || (int) $product['seller_id'] !== (int) $user['id']) {
    flash('error', 'Product not found.');
    redirect('/seller/pages/products/products-list.php');
}

$categories = app(CategoryRepository::class)->allActive();
$images = app(ProductImageRepository::class)->byProduct((int) $product['id']);

$pageTitle = 'Edit product';
$activePage = 'products';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <h1 style="font-size:24px;font-weight:700;margin:0;">Edit product</h1>
    <p style="color:var(--gray-500);margin:4px 0 0;font-size:14px;">Status: <strong style="text-transform:capitalize;color:var(--gray-700);"><?= e($product['status']) ?></strong>
      <?php if ($product['rejection_reason']): ?>
        — Rejection reason: <em><?= e($product['rejection_reason']) ?></em>
      <?php endif; ?>
    </p>
  </div>
  <a href="/seller/pages/products/products-list.php" class="btn btn-outline">← Back</a>
</div>
<?php require __DIR__ . '/../../includes/product-form.php'; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
