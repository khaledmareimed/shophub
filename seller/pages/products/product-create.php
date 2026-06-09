<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CategoryRepository;

$user = require_role('seller');

$categories = app(CategoryRepository::class)->allActive();
$product = null;
$images = [];

$pageTitle = 'New product';
$activePage = 'products';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <h1 style="font-size:24px;font-weight:700;margin:0;">Add a product</h1>
    <p style="color:var(--gray-500);margin:4px 0 0;font-size:14px;">Drafts are private. Submit for review when you're ready.</p>
  </div>
  <a href="/seller/pages/products/products-list.php" class="btn btn-outline">← Back</a>
</div>
<?php require __DIR__ . '/../../includes/product-form.php'; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
