<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\OrderRepository;
use App\Repositories\ReviewRepository;

$user = require_role('customer');

$code = trim((string) ($_GET['code'] ?? ''));
$orderRepo = app(OrderRepository::class);
$order = $code !== '' ? $orderRepo->findByCode($code) : null;
if ($order === null || (int) $order['customer_id'] !== (int) $user['id']) {
    http_response_code(404);
    redirect('/store/pages/account/orders.php');
}

$items = $orderRepo->items((int) $order['id']);
$address = json_decode((string) $order['shipping_address_json'], true) ?: [];

$reviewRepo = app(ReviewRepository::class);
$existingReviews = [];
foreach ($items as $it) {
    $existingReviews[(int) $it['id']] = $reviewRepo->findByOrderItem((int) $it['id']);
}

$cancellable = in_array($order['status'], ['pending', 'paid', 'processing'], true);
$canReview = $order['status'] === 'completed';

$pageTitle = 'Order ' . $order['code'];
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
      <nav style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:13px;color:var(--gray-500);">
        <a href="/store/pages/account/orders.php" style="color:var(--primary);">← Back to orders</a>
      </nav>

      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:24px;font-weight:700;margin:0 0 4px;">Order <?= e($order['code']) ?></h1>
          <div style="font-size:13px;color:var(--gray-500);">Placed on <?= e(date('M j, Y \\a\\t H:i', strtotime((string) $order['placed_at']))) ?></div>
        </div>
        <span style="padding:6px 14px;border-radius:99px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;background:<?= match ($order['status']) { 'completed' => '#dcfce7', 'shipped' => '#dbeafe', 'cancelled' => '#fee2e2', default => '#fef9c3' } ?>;color:<?= match ($order['status']) { 'completed' => '#16a34a', 'shipped' => '#1d4ed8', 'cancelled' => '#dc2626', default => '#ca8a04' } ?>;">
          <?= e($order['status']) ?>
        </span>
      </div>

      <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;overflow:hidden;margin-bottom:16px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--gray-100);font-weight:700;">Items</div>
        <?php foreach ($items as $item):
          $review = $existingReviews[(int) $item['id']] ?? null;
        ?>
          <div style="display:grid;grid-template-columns:64px 1fr auto;gap:14px;align-items:center;padding:14px 20px;border-bottom:1px solid var(--gray-50);">
            <img src="<?= e(upload_url($item['image_path_snapshot'] ?? null)) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px;background:var(--gray-50);">
            <div>
              <div style="font-weight:600;line-height:1.3;"><?= e($item['name_snapshot']) ?></div>
              <div style="font-size:12px;color:var(--gray-500);margin-top:2px;">Qty: <?= e((string) $item['qty']) ?> × <?= e(format_money($item['price_snapshot'])) ?></div>
              <div style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">Fulfillment: <strong><?= e($item['fulfillment_status']) ?></strong></div>
              <?php if ($canReview && $item['fulfillment_status'] === 'delivered' && $review === null): ?>
                <details style="margin-top:8px;">
                  <summary style="cursor:pointer;color:var(--primary);font-size:13px;">Write a review</summary>
                  <form action="/store/actions/review-submit.php" method="post" style="margin-top:8px;background:var(--gray-50);padding:12px;border-radius:6px;">
                    <input type="hidden" name="order_item_id" value="<?= e((string) $item['id']) ?>">
                    <input type="hidden" name="product_id" value="<?= e((string) $item['product_id']) ?>">
                    <div style="margin-bottom:8px;">
                      <label style="font-size:12px;font-weight:600;color:var(--gray-600);display:block;margin-bottom:4px;">Rating</label>
                      <select name="rating" class="form-input" required>
                        <option value="">Choose…</option>
                        <option value="5">★★★★★ — Excellent</option>
                        <option value="4">★★★★☆ — Very good</option>
                        <option value="3">★★★☆☆ — Average</option>
                        <option value="2">★★☆☆☆ — Below average</option>
                        <option value="1">★☆☆☆☆ — Poor</option>
                      </select>
                    </div>
                    <div style="margin-bottom:8px;">
                      <input type="text" name="title" class="form-input" maxlength="120" placeholder="Title (optional)">
                    </div>
                    <div style="margin-bottom:8px;">
                      <textarea name="body" class="form-input" rows="3" maxlength="2000" placeholder="Tell others about your experience…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit review</button>
                  </form>
                </details>
              <?php elseif ($review !== null): ?>
                <div style="margin-top:6px;font-size:12px;color:var(--gray-500);">Your review (<?= e($review['status']) ?>): <?= str_repeat('★', (int) $review['rating']) ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align:right;font-weight:700;"><?= e(format_money($item['line_total'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:20px;">
          <h2 style="font-size:14px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Shipping address</h2>
          <div style="font-size:14px;line-height:1.6;">
            <strong><?= e($address['recipient_name'] ?? '') ?></strong><br>
            <?= e($address['line1'] ?? '') ?><br>
            <?php if (!empty($address['line2'])): ?><?= e($address['line2']) ?><br><?php endif; ?>
            <?= e($address['city'] ?? '') ?>, <?= e($address['postal_code'] ?? '') ?> <?= e($address['country'] ?? '') ?>
          </div>
        </div>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:20px;">
          <h2 style="font-size:14px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Totals</h2>
          <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;"><span>Subtotal</span><strong><?= e(format_money($order['subtotal'])) ?></strong></div>
          <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;"><span>Shipping</span><strong><?= e(format_money($order['shipping_fee'])) ?></strong></div>
          <?php if ((float) $order['discount_total'] > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:3px 0;color:var(--success);"><span>Discount</span><strong>−<?= e(format_money($order['discount_total'])) ?></strong></div>
          <?php endif; ?>
          <div style="border-top:2px solid var(--gray-100);margin-top:6px;padding-top:6px;display:flex;justify-content:space-between;font-size:16px;font-weight:800;">
            <span>Total</span><span><?= e(format_money($order['grand_total'])) ?></span>
          </div>
          <div style="font-size:12px;color:var(--gray-500);margin-top:8px;">Payment: <?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)</div>
        </div>
      </div>

      <?php if ($cancellable): ?>
        <form action="/store/actions/order-cancel.php" method="post" style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:20px;">
          <input type="hidden" name="order_id" value="<?= e((string) $order['id']) ?>">
          <h2 style="font-size:14px;font-weight:700;margin-bottom:8px;">Cancel order</h2>
          <textarea name="reason" class="form-input" rows="2" placeholder="Reason for cancellation (optional)"></textarea>
          <button type="submit" class="btn btn-outline" style="margin-top:8px;color:#dc2626;border-color:#fecaca;" onclick="return confirm('Cancel this order?');">Cancel order</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
