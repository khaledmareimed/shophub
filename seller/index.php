<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\ReportService;

$user = require_role('seller');

$pdo = \App\Core\Database::pdo();

$st = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ? AND deleted_at IS NULL');
$st->execute([(int) $user['id']]);
$productCount = (int) $st->fetchColumn();

$st = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ? AND deleted_at IS NULL AND status = "active"');
$st->execute([(int) $user['id']]);
$activeCount = (int) $st->fetchColumn();

$st = $pdo->prepare('SELECT COUNT(*), COALESCE(SUM(line_total),0) FROM order_items WHERE seller_id = ?');
$st->execute([(int) $user['id']]);
[$lineCount, $lineRevenue] = $st->fetch(\PDO::FETCH_NUM);

$st = $pdo->prepare(
    'SELECT oi.*, o.code AS order_code, o.placed_at, o.status order_status
     FROM order_items oi JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? ORDER BY o.placed_at DESC LIMIT 5'
);
$st->execute([(int) $user['id']]);
$recentLines = $st->fetchAll();

$st = $pdo->prepare(
    'SELECT DATE(o.placed_at) d, COALESCE(SUM(oi.line_total),0) total
     FROM order_items oi JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? AND o.placed_at >= (UTC_DATE() - INTERVAL 13 DAY)
     GROUP BY DATE(o.placed_at) ORDER BY d ASC'
);
$st->execute([(int) $user['id']]);
$salesByDay = $st->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/layout-start.php';
?>
<div class="page-header" style="margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;margin:0 0 4px;">Welcome back, <?= e(explode(' ', $user['name'])[0]) ?></h1>
  <p style="color:var(--gray-500);margin:0;">Here's how your store is doing.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:8px;">Total products</div>
    <div style="font-size:28px;font-weight:800;"><?= e((string) $productCount) ?></div>
    <div style="font-size:12px;color:var(--gray-500);margin-top:4px;"><?= e((string) $activeCount) ?> active</div>
  </div>
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:8px;">Lines sold</div>
    <div style="font-size:28px;font-weight:800;"><?= e((string) (int) $lineCount) ?></div>
  </div>
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:8px;">Revenue (gross)</div>
    <div style="font-size:28px;font-weight:800;"><?= e(format_money((string) $lineRevenue)) ?></div>
  </div>
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:8px;">Status</div>
    <div style="font-size:18px;font-weight:700;color:<?= $user['status'] === 'active' ? '#16a34a' : '#dc2626' ?>;text-transform:capitalize;"><?= e($user['status']) ?></div>
  </div>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;margin-bottom:24px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Sales — last 14 days</h2>
  <?php if ($salesByDay === []): ?>
    <p style="color:var(--gray-500);font-size:14px;">No sales yet.</p>
  <?php else: ?>
    <?php
      $max = 0.0;
      foreach ($salesByDay as $row) $max = max($max, (float) $row['total']);
      $max = max($max, 1.0);
    ?>
    <div style="display:flex;align-items:flex-end;gap:8px;height:160px;">
      <?php foreach ($salesByDay as $row):
        $h = max(2, (int) round(((float) $row['total'] / $max) * 140));
      ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
          <div style="width:100%;height:<?= $h ?>px;background:linear-gradient(180deg,var(--primary-color),#7c1d1d);border-radius:4px 4px 0 0;" title="<?= e($row['d']) ?>: <?= e(format_money((string) $row['total'])) ?>"></div>
          <div style="font-size:10px;color:var(--gray-500);transform:rotate(-45deg);transform-origin:center;white-space:nowrap;"><?= e(date('M j', strtotime((string) $row['d']))) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
  <div style="padding:16px 24px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
    <h2 style="font-size:16px;font-weight:700;margin:0;">Recent orders</h2>
    <a href="/seller/pages/orders/orders-list.php" style="color:var(--primary-color);font-size:13px;text-decoration:none;">View all →</a>
  </div>
  <?php if ($recentLines === []): ?>
    <div style="padding:40px 24px;text-align:center;color:var(--gray-500);">No orders yet.</div>
  <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:12px 24px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Order</th>
          <th style="padding:12px 24px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Product</th>
          <th style="padding:12px 24px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Qty</th>
          <th style="padding:12px 24px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Total</th>
          <th style="padding:12px 24px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentLines as $l): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:12px 24px;"><a href="/seller/pages/orders/order-details.php?code=<?= e($l['order_code']) ?>" style="color:var(--primary-color);font-weight:600;"><?= e($l['order_code']) ?></a></td>
            <td style="padding:12px 24px;"><?= e($l['name_snapshot']) ?></td>
            <td style="padding:12px 24px;"><?= e((string) $l['qty']) ?></td>
            <td style="padding:12px 24px;font-weight:600;"><?= e(format_money($l['line_total'])) ?></td>
            <td style="padding:12px 24px;"><span style="text-transform:capitalize;"><?= e($l['fulfillment_status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/layout-end.php'; ?>
