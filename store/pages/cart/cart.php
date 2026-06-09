<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\ProductImageRepository;
use App\Services\CartService;

$user = require_role('customer');

$totals = app(CartService::class)->totals((int) $user['id']);
$lines = $totals['lines'];
$subtotal = $totals['subtotal'];

$activeLines = array_values(array_filter($lines, static fn ($l) => ($l['status'] ?? '') === 'active'));
$itemCount = array_sum(array_map(static fn ($l) => (int) $l['qty'], $activeLines));
$shipping = (float) $subtotal >= 50.0 ? '0.00' : '5.99';
$grand = \App\Core\Decimal::add($subtotal, $shipping);

$pageTitle = 'Shopping cart';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <nav class="breadcrumb" style="margin-bottom:16px;">
    <a href="/store/index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">Shopping Cart</span>
  </nav>

  <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">
    Shopping Cart <?php if ($itemCount > 0): ?><span style="color:var(--gray-400);font-weight:400;font-size:18px;">(<?= e((string) $itemCount) ?> items)</span><?php endif; ?>
  </h1>

  <?php if ($lines === []): ?>
    <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:80px 24px;text-align:center;">
      <h2 style="font-size:20px;font-weight:600;margin-bottom:8px;"><?= e(t('cart.empty')) ?></h2>
      <p style="color:var(--gray-500);margin-bottom:24px;">Discover products from top sellers and start shopping.</p>
      <a href="/store/pages/catalog/products.php" class="btn btn-primary btn-lg">Browse products</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;">
      <div>
        <?php foreach ($lines as $line):
          $img = app(ProductImageRepository::class)->byProduct((int) $line['product_id']);
          $imgUrl = $img !== [] ? upload_url((string) $img[0]['path']) : '/store/assets/images/placeholder.svg';
          $unit = (string) $line['price_snapshot'];
          $qty = (int) $line['qty'];
          $isActive = ($line['status'] ?? '') === 'active';
          $lineTotal = $isActive ? \App\Core\Decimal::mul($unit, (string) $qty) : '0.00';
          $name = (string) $line['name'];
        ?>
          <div style="display:grid;grid-template-columns:88px 1fr auto;gap:20px;align-items:center;padding:20px;background:#fff;border:1px solid var(--gray-100);margin-bottom:8px;border-radius:8px;<?= !$isActive ? 'opacity:0.6' : '' ?>">
            <a href="/store/pages/product/product-detail.php?slug=<?= e($line['slug']) ?>" style="display:block;width:88px;height:88px;background:var(--gray-50);border-radius:6px;overflow:hidden;">
              <img src="<?= e($imgUrl) ?>" alt="<?= e($name) ?>" style="width:100%;height:100%;object-fit:cover;">
            </a>
            <div>
              <div style="font-weight:600;color:var(--navy);margin-bottom:4px;line-height:1.3;">
                <a href="/store/pages/product/product-detail.php?slug=<?= e($line['slug']) ?>" style="color:inherit;text-decoration:none;"><?= e($name) ?></a>
              </div>
              <?php if (!$isActive): ?>
                <div style="font-size:12px;color:#dc2626;margin-top:4px;">This product is no longer available.</div>
              <?php else: ?>
                <div style="font-size:13px;color:var(--gray-500);margin-bottom:8px;"><?= e(format_money($unit)) ?> each</div>
                <form action="/store/actions/cart-update.php" method="post" style="display:flex;align-items:center;gap:8px;margin:8px 0;">
                  <input type="hidden" name="item_id" value="<?= e((string) $line['id']) ?>">
                  <div class="qty-control" style="display:inline-flex;border:1.5px solid var(--gray-200);border-radius:6px;overflow:hidden;">
                    <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.closest('form').submit();">−</button>
                    <input type="number" class="qty-input" name="qty" value="<?= e((string) $qty) ?>" min="1" style="width:50px;border:0;text-align:center;">
                    <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.closest('form').submit();">+</button>
                  </div>
                  <button type="submit" class="btn btn-outline btn-sm" style="font-size:12px;">Update</button>
                </form>
              <?php endif; ?>
              <form action="/store/actions/cart-remove.php" method="post" style="display:inline">
                <input type="hidden" name="item_id" value="<?= e((string) $line['id']) ?>">
                <button type="submit" style="background:none;border:0;font-size:12px;color:var(--gray-500);text-decoration:underline;cursor:pointer;padding:0;">Remove</button>
              </form>
            </div>
            <div style="text-align:right;">
              <div style="font-size:18px;font-weight:700;color:var(--navy);"><?= e(format_money($lineTotal)) ?></div>
            </div>
          </div>
        <?php endforeach; ?>

        <form action="/store/actions/cart-clear.php" method="post" style="margin-top:16px;">
          <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Empty your entire cart?');">Clear cart</button>
        </form>
      </div>

      <aside>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;position:sticky;top:80px;">
          <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Order Summary</h2>

          <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:var(--gray-600);">
            <span><?= e(t('cart.subtotal')) ?></span>
            <strong><?= e(format_money($subtotal)) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:var(--gray-600);">
            <span><?= e(t('cart.shipping')) ?></span>
            <strong>
              <?= (float) $shipping > 0 ? e(format_money($shipping)) : '<span style="color:var(--success);">FREE</span>' ?>
            </strong>
          </div>
          <?php if ((float) $shipping > 0): ?>
            <div style="font-size:12px;color:var(--gray-500);background:var(--gray-50);padding:8px;border-radius:4px;margin:8px 0;">
              Add <?= e(format_money(\App\Core\Decimal::sub('50', $subtotal))) ?> more for free shipping
            </div>
          <?php endif; ?>
          <div style="border-top:2px solid var(--gray-100);margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;font-size:18px;font-weight:800;">
            <span><?= e(t('cart.total')) ?></span>
            <span><?= e(format_money($grand)) ?></span>
          </div>

          <a href="/store/pages/checkout/checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top:16px;<?= $activeLines === [] ? 'pointer-events:none;opacity:0.5;' : '' ?>">
            Proceed to Checkout
          </a>
          <a href="/store/pages/catalog/products.php" class="btn btn-outline btn-block" style="margin-top:8px;">Continue shopping</a>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
