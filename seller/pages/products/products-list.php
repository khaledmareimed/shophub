<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\ProductRepository;

$user = require_role('seller');

$status = (string) ($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 20;
[$rows, $total] = app(ProductRepository::class)->searchSeller(
    (int) $user['id'],
    $status === 'all' ? null : $status,
    $page,
    $per
);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'My products';
$activePage = 'products';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <h1 style="font-size:24px;font-weight:700;margin:0;">My products</h1>
  <a href="/seller/pages/products/product-create.php" class="btn btn-primary">+ Add product</a>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach (['all' => 'All', 'draft' => 'Drafts', 'pending' => 'Pending', 'active' => 'Active', 'rejected' => 'Rejected', 'archived' => 'Archived'] as $key => $label): ?>
    <a href="?status=<?= e($key) ?>" class="btn <?= $status === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($rows === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;">
    <h2 style="font-size:18px;font-weight:600;margin-bottom:8px;">No products yet</h2>
    <a href="/seller/pages/products/product-create.php" class="btn btn-primary" style="margin-top:8px;">Add your first product</a>
  </div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Product</th>
          <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Price</th>
          <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Stock</th>
          <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Sold</th>
          <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
          <th style="padding:12px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $p): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:12px 16px;font-weight:600;line-height:1.3;"><?= e($p['name']) ?></td>
            <td style="padding:12px 16px;"><?= e(format_money($p['price'])) ?></td>
            <td style="padding:12px 16px;"><?= e((string) $p['stock']) ?></td>
            <td style="padding:12px 16px;"><?= e((string) $p['sold_count']) ?></td>
            <td style="padding:12px 16px;text-transform:capitalize;"><?= e($p['status']) ?></td>
            <td style="padding:12px 16px;text-align:right;">
              <a href="/seller/pages/products/product-edit.php?id=<?= e((string) $p['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav style="display:flex;justify-content:center;gap:8px;margin-top:16px;">
      <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="?status=<?= e($status) ?>&page=<?= $page - 1 ?>">←</a><?php endif; ?>
      <span style="padding:6px 12px;color:var(--gray-600);">Page <?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="btn btn-outline btn-sm" href="?status=<?= e($status) ?>&page=<?= $page + 1 ?>">→</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
