<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\WishlistRepository;

$user = require_role('customer');
$rows = app(WishlistRepository::class)->list((int) $user['id']);

$pageTitle = 'My Wishlist';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <nav class="breadcrumb" style="margin-bottom:16px;">
    <a href="/store/index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">My Wishlist</span>
  </nav>

  <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">
    My Wishlist
    <?php if ($rows !== []): ?>
      <span style="color:var(--gray-400);font-weight:400;font-size:18px;">(<?= e((string) count($rows)) ?>)</span>
    <?php endif; ?>
  </h1>

  <?php if ($rows === []): ?>
    <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:80px 24px;text-align:center;">
      <h2 style="font-size:20px;font-weight:600;margin-bottom:8px;">Your wishlist is empty</h2>
      <p style="color:var(--gray-500);margin-bottom:24px;">Heart your favourites and we'll keep them here for later.</p>
      <a href="/store/pages/catalog/products.php" class="btn btn-primary btn-lg">Browse products</a>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($rows as $row):
        $product = app(ProductRepository::class)->findById((int) $row['product_id']);
        if ($product === null) continue;
        require __DIR__ . '/../../includes/product-card.php';
      endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
