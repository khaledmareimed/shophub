<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;
use App\Repositories\WishlistRepository;

$user = require_role('customer');

$orderRepo = app(OrderRepository::class);
[$orders, $totalOrders] = $orderRepo->listCustomer((int) $user['id'], 1, 5);

$lifetime = '0.00';
$pending = 0;
$delivered = 0;
foreach ($orders as $o) {
    $lifetime = \App\Core\Decimal::add($lifetime, (string) $o['grand_total']);
    if (in_array($o['status'], ['pending', 'paid', 'processing', 'shipped'], true)) {
        $pending++;
    }
    if ($o['status'] === 'completed') {
        $delivered++;
    }
}
$wishCount = count(app(WishlistRepository::class)->list((int) $user['id']));

$pageTitle = 'My account';
$activePage = 'dashboard';
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
      <h1 style="font-size:26px;font-weight:800;margin-bottom:4px;">Hello, <?= e(explode(' ', $user['name'])[0]) ?></h1>
      <p style="color:var(--gray-500);margin-bottom:32px;">Welcome back! Here's a quick look at your account.</p>

      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;text-align:center;">
          <div style="font-size:30px;font-weight:800;color:var(--primary);margin-bottom:4px;"><?= e((string) $totalOrders) ?></div>
          <div style="font-size:12px;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Total orders</div>
        </div>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;text-align:center;">
          <div style="font-size:30px;font-weight:800;color:var(--primary);margin-bottom:4px;"><?= e((string) $pending) ?></div>
          <div style="font-size:12px;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">In progress</div>
        </div>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;text-align:center;">
          <div style="font-size:30px;font-weight:800;color:var(--primary);margin-bottom:4px;"><?= e((string) $wishCount) ?></div>
          <div style="font-size:12px;color:var(--gray-500);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Wishlisted</div>
        </div>
      </div>

      <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--gray-100);">
          <h2 style="font-size:16px;font-weight:700;margin:0;">Recent orders</h2>
          <a href="/store/pages/account/orders.php" style="font-size:13px;color:var(--primary);">View all →</a>
        </div>
        <?php if ($orders === []): ?>
          <div style="padding:48px 24px;text-align:center;color:var(--gray-500);">
            <p>No orders yet.</p>
            <a href="/store/pages/catalog/products.php" class="btn btn-primary" style="margin-top:12px;">Browse products</a>
          </div>
        <?php else: ?>
          <?php foreach ($orders as $o): ?>
            <a href="/store/pages/account/order-detail.php?code=<?= e($o['code']) ?>" style="display:grid;grid-template-columns:1fr auto auto auto;gap:16px;align-items:center;padding:16px 24px;border-bottom:1px solid var(--gray-50);font-size:14px;text-decoration:none;color:inherit;">
              <div>
                <div style="font-weight:600;color:var(--primary);"><?= e($o['code']) ?></div>
                <div style="font-size:12px;color:var(--gray-500);"><?= e(date('M j, Y', strtotime((string) $o['placed_at']))) ?></div>
              </div>
              <span class="status-badge status-<?= e($o['status']) ?>" style="padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:<?= match ($o['status']) { 'completed' => '#dcfce7', 'shipped' => '#dbeafe', 'cancelled' => '#fee2e2', default => '#fef9c3' } ?>;color:<?= match ($o['status']) { 'completed' => '#16a34a', 'shipped' => '#1d4ed8', 'cancelled' => '#dc2626', default => '#ca8a04' } ?>;text-transform:uppercase;letter-spacing:0.05em;">
                <?= e($o['status']) ?>
              </span>
              <span style="font-weight:700;color:var(--navy);"><?= e(format_money($o['grand_total'])) ?></span>
              <span style="color:var(--primary);font-size:18px;">›</span>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
