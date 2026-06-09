<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;

$user = require_role('customer');

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 10;
[$orders, $total] = app(OrderRepository::class)->listCustomer((int) $user['id'], $page, $per);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'My orders';
$activePage = 'orders';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;">
    <?php require __DIR__ . '/../../includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">My orders</h1>

      <?php if ($orders === []): ?>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:60px 24px;text-align:center;">
          <h2 style="font-size:18px;font-weight:600;margin-bottom:8px;">No orders yet</h2>
          <p style="color:var(--gray-500);margin-bottom:20px;">Start shopping to see your orders here.</p>
          <a href="/store/pages/catalog/products.php" class="btn btn-primary">Browse products</a>
        </div>
      <?php else: ?>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;overflow:hidden;">
          <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead style="background:var(--gray-50);">
              <tr style="text-align:left;">
                <th style="padding:14px 20px;font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;">Order</th>
                <th style="padding:14px 20px;font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;">Date</th>
                <th style="padding:14px 20px;font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;">Status</th>
                <th style="padding:14px 20px;font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;">Total</th>
                <th style="padding:14px 20px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr style="border-top:1px solid var(--gray-100);">
                  <td style="padding:14px 20px;"><a href="/store/pages/account/order-detail.php?code=<?= e($o['code']) ?>" style="color:var(--primary);font-weight:600;"><?= e($o['code']) ?></a></td>
                  <td style="padding:14px 20px;color:var(--gray-500);"><?= e(date('M j, Y', strtotime((string) $o['placed_at']))) ?></td>
                  <td style="padding:14px 20px;">
                    <span style="padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:<?= match ($o['status']) { 'completed' => '#dcfce7', 'shipped' => '#dbeafe', 'cancelled' => '#fee2e2', default => '#fef9c3' } ?>;color:<?= match ($o['status']) { 'completed' => '#16a34a', 'shipped' => '#1d4ed8', 'cancelled' => '#dc2626', default => '#ca8a04' } ?>;text-transform:uppercase;letter-spacing:0.05em;">
                      <?= e($o['status']) ?>
                    </span>
                  </td>
                  <td style="padding:14px 20px;font-weight:700;"><?= e(format_money($o['grand_total'])) ?></td>
                  <td style="padding:14px 20px;text-align:right;">
                    <a href="/store/pages/account/order-detail.php?code=<?= e($o['code']) ?>" class="btn btn-outline btn-sm">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPages > 1): ?>
          <nav style="display:flex;justify-content:center;gap:8px;margin-top:24px;">
            <?php if ($page > 1): ?>
              <a class="btn btn-outline btn-sm" href="?page=<?= $page - 1 ?>">← Previous</a>
            <?php endif; ?>
            <span style="display:inline-flex;align-items:center;padding:0 12px;color:var(--gray-600);">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
              <a class="btn btn-outline btn-sm" href="?page=<?= $page + 1 ?>">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
