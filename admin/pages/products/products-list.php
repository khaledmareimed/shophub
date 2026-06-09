<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\ProductRepository;

require_role('admin');

$status = (string) ($_GET['status'] ?? 'pending');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;

[$rows, $total] = app(ProductRepository::class)->searchAdmin($status === 'all' ? null : $status, $page, $per);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Products';
$activePage = 'products';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Products</h1>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach (['pending' => 'Pending review', 'all' => 'All', 'active' => 'Active', 'rejected' => 'Rejected', 'draft' => 'Draft', 'outofstock' => 'Out of stock'] as $k => $v): ?>
    <a href="?status=<?= e($k) ?>" class="btn <?= $status === $k ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($v) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($rows === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;color:var(--gray-500);">No products found.</div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Name</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Price</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Stock</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $p): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;"><strong><?= e($p['name']) ?></strong><br><span style="color:var(--gray-500);font-size:12px;">SKU <?= e($p['sku'] ?? '—') ?></span></td>
            <td style="padding:10px 16px;"><?= e(format_money($p['price'])) ?></td>
            <td style="padding:10px 16px;"><?= e((string) $p['stock']) ?></td>
            <td style="padding:10px 16px;text-transform:capitalize;"><?= e($p['status']) ?></td>
            <td style="padding:10px 16px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
              <?php if ($p['status'] === 'pending'): ?>
                <form action="/admin/actions/product-moderate.php" method="post" style="margin:0;">
                  <input type="hidden" name="id" value="<?= e((string) $p['id']) ?>">
                  <button name="action" value="approve" class="btn btn-primary btn-sm">Approve</button>
                </form>
                <form action="/admin/actions/product-moderate.php" method="post" style="margin:0;display:flex;gap:4px;" onsubmit="this.querySelector('input[name=reason]').value = prompt('Rejection reason?','') || '';">
                  <input type="hidden" name="id" value="<?= e((string) $p['id']) ?>">
                  <input type="hidden" name="reason" value="">
                  <button name="action" value="reject" class="btn btn-outline btn-sm">Reject</button>
                </form>
              <?php elseif ($p['status'] === 'active'): ?>
                <form action="/admin/actions/product-moderate.php" method="post" style="margin:0;" onsubmit="return confirm('Archive this product?');">
                  <input type="hidden" name="id" value="<?= e((string) $p['id']) ?>">
                  <button name="action" value="archive" class="btn btn-outline btn-sm">Archive</button>
                </form>
              <?php endif; ?>
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
