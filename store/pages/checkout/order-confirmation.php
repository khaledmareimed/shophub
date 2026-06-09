<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;

$user = require_role('customer');

$code = trim((string) ($_GET['code'] ?? ''));
if ($code === '') {
    redirect('/store/pages/account/orders.php');
}

$order = app(OrderRepository::class)->findByCode($code);
if ($order === null || (int) $order['customer_id'] !== (int) $user['id']) {
    http_response_code(404);
    $pageTitle = 'Order not found';
    ?><!DOCTYPE html><html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
    <head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
    <body>
    <?php require __DIR__ . '/../../includes/topnav.php'; ?>
    <div class="container" style="padding:60px 20px;text-align:center;">
      <h1>Order not found</h1>
      <a href="/store/pages/account/orders.php" class="btn btn-primary">View my orders</a>
    </div>
    <?php require __DIR__ . '/../../includes/footer.php'; ?>
    </body></html>
    <?php
    exit;
}

$items = app(OrderRepository::class)->items((int) $order['id']);
$address = json_decode((string) $order['shipping_address_json'], true) ?: [];

$pageTitle = 'Order ' . $order['code'] . ' — Confirmed';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:32px;padding-bottom:48px;max-width:760px;">
  <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:32px;text-align:center;margin-bottom:24px;">
    <div style="width:64px;height:64px;background:#dcfce7;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>
    <h1 style="font-size:26px;font-weight:800;margin-bottom:8px;">Thank you for your order!</h1>
    <p style="color:var(--gray-500);">Your order <strong style="color:var(--navy);"><?= e($order['code']) ?></strong> has been placed.</p>
    <p style="color:var(--gray-500);font-size:14px;margin-top:6px;">We'll deliver it cash on delivery within a few business days.</p>
  </div>

  <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;margin-bottom:16px;">
    <h2 style="font-size:18px;font-weight:700;margin-bottom:12px;">Items</h2>
    <?php foreach ($items as $item): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--gray-50);">
        <img src="<?= e(upload_url($item['image_path_snapshot'] ?? null)) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;background:var(--gray-50);">
        <div style="flex:1;">
          <div style="font-size:14px;font-weight:600;line-height:1.3;"><?= e($item['name_snapshot']) ?></div>
          <div style="font-size:12px;color:var(--gray-500);">Qty: <?= e((string) $item['qty']) ?> × <?= e(format_money($item['price_snapshot'])) ?></div>
        </div>
        <div style="font-size:14px;font-weight:700;"><?= e(format_money($item['line_total'])) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:20px;">
      <h2 style="font-size:14px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Shipping to</h2>
      <div style="font-size:14px;line-height:1.6;">
        <strong><?= e($address['recipient_name'] ?? '') ?></strong><br>
        <?= e($address['line1'] ?? '') ?><br>
        <?php if (!empty($address['line2'])): ?><?= e($address['line2']) ?><br><?php endif; ?>
        <?= e($address['city'] ?? '') ?>, <?= e($address['postal_code'] ?? '') ?> <?= e($address['country'] ?? '') ?><br>
        <?php if (!empty($address['phone'])): ?><span style="color:var(--gray-500);font-size:13px;"><?= e($address['phone']) ?></span><?php endif; ?>
      </div>
    </div>
    <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:20px;">
      <h2 style="font-size:14px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Totals</h2>
      <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;"><span>Subtotal</span><strong><?= e(format_money($order['subtotal'])) ?></strong></div>
      <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;"><span>Shipping</span><strong><?= e(format_money($order['shipping_fee'])) ?></strong></div>
      <?php if ((float) $order['discount_total'] > 0): ?>
        <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;color:var(--success);"><span>Discount</span><strong>−<?= e(format_money($order['discount_total'])) ?></strong></div>
      <?php endif; ?>
      <div style="border-top:2px solid var(--gray-100);margin-top:6px;padding-top:6px;display:flex;justify-content:space-between;font-size:16px;font-weight:800;">
        <span>Total</span><span><?= e(format_money($order['grand_total'])) ?></span>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:12px;justify-content:center;">
    <a href="/store/pages/account/orders.php" class="btn btn-outline">View my orders</a>
    <a href="/store/pages/catalog/products.php" class="btn btn-primary">Continue shopping</a>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
