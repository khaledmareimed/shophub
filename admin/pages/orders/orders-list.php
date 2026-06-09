<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;

require_role('admin');

$status = (string) ($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;

[$rows, $total] = app(OrderRepository::class)->listAdmin($status === 'all' ? null : $status, $page, $per);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Orders';
$activePage = 'orders';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Orders</h1>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach (['all' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $v): ?>
    <a href="?status=<?= e($k) ?>" class="btn <?= $status === $k ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($v) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($rows === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;color:var(--gray-500);">No orders found.</div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Order</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Date</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Total</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Payment</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $o): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;"><a href="/admin/pages/orders/order-details.php?code=<?= e($o['code']) ?>" style="color:var(--primary-color);font-weight:600;"><?= e($o['code']) ?></a></td>
            <td style="padding:10px 16px;font-size:13px;color:var(--gray-500);"><?= e(date('Y-m-d', strtotime((string) $o['placed_at']))) ?></td>
            <td style="padding:10px 16px;font-weight:600;"><?= e(format_money($o['grand_total'])) ?></td>
            <td style="padding:10px 16px;text-transform:capitalize;"><?= e($o['status']) ?></td>
            <td style="padding:10px 16px;text-transform:capitalize;font-size:13px;color:var(--gray-500);"><?= e($o['payment_status']) ?></td>
            <td style="padding:10px 16px;text-align:right;"><a href="/admin/pages/orders/order-details.php?code=<?= e($o['code']) ?>" class="btn btn-outline btn-sm">View</a></td>
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
