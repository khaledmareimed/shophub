<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

require_role('admin');

$code = (string) ($_GET['code'] ?? '');
$repo = app(OrderRepository::class);
$order = $code !== '' ? $repo->findByCode($code) : null;
if (!$order) {
    flash('error', 'Order not found.');
    redirect('/admin/pages/orders/orders-list.php');
}

$items = $repo->items((int) $order['id']);
$customer = app(UserRepository::class)->findById((int) $order['customer_id']);
$shippingAddr = json_decode((string) $order['shipping_address_json'], true) ?: [];

$pageTitle = 'Order ' . $order['code'];
$activePage = 'orders';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <a href="/admin/pages/orders/orders-list.php" style="color:var(--gray-500);text-decoration:none;font-size:13px;">← Orders</a>
    <h1 style="font-size:24px;font-weight:700;margin:4px 0 0;"><?= e($order['code']) ?></h1>
    <p style="color:var(--gray-500);font-size:13px;margin:4px 0 0;">Placed <?= e(date('M j, Y H:i', strtotime((string) $order['placed_at']))) ?></p>
  </div>
  <form action="/admin/actions/order-status.php" method="post" style="margin:0;display:flex;gap:6px;">
    <input type="hidden" name="id" value="<?= e((string) $order['id']) ?>">
    <select name="status" class="form-input" style="padding:6px 10px;font-size:13px;border:1px solid var(--gray-300);border-radius:6px;">
      <?php foreach (['pending', 'processing', 'completed', 'cancelled'] as $s): ?>
        <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-sm">Update status</button>
  </form>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 12px;">Items (<?= e((string) count($items)) ?>)</h2>
    <?php foreach ($items as $i): ?>
      <div style="display:flex;gap:12px;padding:10px 0;border-top:1px solid var(--gray-100);">
        <img src="<?= e(upload_url($i['image_path_snapshot'] ?? null)) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;background:var(--gray-100);">
        <div style="flex:1;">
          <div style="font-weight:600;"><?= e($i['name_snapshot']) ?></div>
          <div style="color:var(--gray-500);font-size:12px;">Qty <?= e((string) $i['qty']) ?> · seller #<?= e((string) $i['seller_id']) ?> · <span style="text-transform:capitalize;"><?= e($i['fulfillment_status']) ?></span></div>
        </div>
        <div style="font-weight:700;"><?= e(format_money($i['line_total'])) ?></div>
      </div>
    <?php endforeach; ?>
    <hr style="border:0;border-top:1px solid var(--gray-100);margin:16px 0;">
    <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Subtotal</span><span><?= e(format_money($order['subtotal'])) ?></span></div>
    <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Shipping</span><span><?= e(format_money($order['shipping_fee'])) ?></span></div>
    <?php if ((float) $order['discount_total'] > 0): ?>
      <div style="display:flex;justify-content:space-between;font-size:13px;color:#16a34a;"><span>Discount</span><span>-<?= e(format_money($order['discount_total'])) ?></span></div>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:1px solid var(--gray-100);padding-top:8px;margin-top:8px;"><span>Total</span><span><?= e(format_money($order['grand_total'])) ?></span></div>
  </div>

  <div>
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;margin-bottom:16px;">
      <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;">Customer</h3>
      <div style="font-size:13px;line-height:1.6;">
        <?= e((string) ($customer['name'] ?? '—')) ?><br>
        <?= e((string) ($customer['email'] ?? '')) ?>
      </div>
    </div>
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;margin-bottom:16px;">
      <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;">Shipping address</h3>
      <div style="font-size:13px;line-height:1.6;">
        <?= e((string) ($shippingAddr['recipient_name'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['line1'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['city'] ?? '')) ?>, <?= e((string) ($shippingAddr['region'] ?? '')) ?> <?= e((string) ($shippingAddr['postal_code'] ?? '')) ?><br>
        <?= e((string) ($shippingAddr['country'] ?? '')) ?>
      </div>
    </div>
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;">
      <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;">Payment</h3>
      <div style="font-size:13px;line-height:1.6;">
        Method: <strong style="text-transform:uppercase;"><?= e($order['payment_method']) ?></strong><br>
        Status: <strong style="text-transform:capitalize;"><?= e($order['payment_status']) ?></strong>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
