<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;

$user = require_role('seller');

$code = (string) ($_GET['code'] ?? '');
$repo = app(OrderRepository::class);
$order = $code !== '' ? $repo->findByCode($code) : null;
if (!$order) {
    flash('error', 'Order not found.');
    redirect('/seller/pages/orders/orders-list.php');
}

$allItems = $repo->items((int) $order['id']);
$items = array_values(array_filter($allItems, static fn($i) => (int) $i['seller_id'] === (int) $user['id']));
if ($items === []) {
    flash('error', 'Order not found.');
    redirect('/seller/pages/orders/orders-list.php');
}
$shippingAddr = json_decode((string) $order['shipping_address_json'], true) ?: [];
$flowOptions = ['pending', 'processing', 'shipped', 'delivered'];

$pageTitle = 'Order ' . $order['code'];
$activePage = 'orders';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <a href="/seller/pages/orders/orders-list.php" style="color:var(--gray-500);text-decoration:none;font-size:13px;">← My orders</a>
    <h1 style="font-size:24px;font-weight:700;margin:4px 0 0;"><?= e($order['code']) ?></h1>
    <p style="color:var(--gray-500);font-size:13px;margin:4px 0 0;">Placed <?= e(date('M j, Y', strtotime((string) $order['placed_at']))) ?> · Order status: <strong style="text-transform:capitalize;color:var(--gray-700);"><?= e($order['status']) ?></strong></p>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Your line items</h2>
    <?php foreach ($items as $i): ?>
      <div style="display:flex;gap:12px;padding:12px 0;border-top:1px solid var(--gray-100);">
        <img src="<?= e(upload_url($i['image_path_snapshot'] ?? null)) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px;background:var(--gray-100);">
        <div style="flex:1;">
          <div style="font-weight:600;"><?= e($i['name_snapshot']) ?></div>
          <div style="color:var(--gray-500);font-size:13px;">Qty <?= e((string) $i['qty']) ?> · <?= e(format_money($i['line_total'])) ?></div>
          <div style="color:var(--gray-600);font-size:13px;margin-top:4px;">Fulfillment: <strong style="text-transform:capitalize;"><?= e($i['fulfillment_status']) ?></strong></div>

          <?php if ($order['status'] !== 'cancelled' && $i['fulfillment_status'] !== 'delivered'): ?>
            <form action="/seller/actions/order-fulfill.php" method="post" style="margin-top:8px;display:flex;gap:8px;align-items:center;">
              <input type="hidden" name="item_id" value="<?= e((string) $i['id']) ?>">
              <select name="status" class="form-input" style="padding:6px 10px;font-size:13px;border:1px solid var(--gray-300);border-radius:6px;">
                <?php foreach ($flowOptions as $f): ?>
                  <option value="<?= e($f) ?>" <?= $i['fulfillment_status'] === $f ? 'selected' : '' ?>><?= e(ucfirst($f)) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div>
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:20px;margin-bottom:16px;">
      <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;">Customer</h3>
      <div style="font-size:13px;line-height:1.6;">
        <?= e((string) ($shippingAddr['recipient_name'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['phone'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['line1'] ?? '')) ?><br>
        <?php if (!empty($shippingAddr['line2'])): ?><?= e($shippingAddr['line2']) ?><br><?php endif; ?>
        <?= e((string) ($shippingAddr['city'] ?? '')) ?>, <?= e((string) ($shippingAddr['region'] ?? '')) ?> <?= e((string) ($shippingAddr['postal_code'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['country'] ?? '')) ?>
      </div>
    </div>
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:20px;">
      <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;">Payment</h3>
      <div style="font-size:13px;line-height:1.6;">
        Method: <strong style="text-transform:uppercase;"><?= e($order['payment_method']) ?></strong><br>
        Status: <strong style="text-transform:capitalize;"><?= e($order['payment_status']) ?></strong>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
