<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\AddressRepository;
use App\Repositories\ProductImageRepository;
use App\Services\CartService;
use App\Services\CheckoutService;

$user = require_role('customer');

$totals = app(CartService::class)->totals((int) $user['id']);
if ($totals['lines'] === []) {
    flash('error', 'Your cart is empty.');
    redirect('/store/pages/cart/cart.php');
}
$activeLines = array_values(array_filter($totals['lines'], static fn ($l) => ($l['status'] ?? '') === 'active'));
if ($activeLines === []) {
    flash('error', 'No active items to checkout.');
    redirect('/store/pages/cart/cart.php');
}

$couponCode = trim((string) ($_GET['coupon'] ?? $_POST['coupon'] ?? ''));
$preview = app(CheckoutService::class)->preview((int) $user['id'], $couponCode !== '' ? $couponCode : null);

$addresses = app(AddressRepository::class)->listForUser((int) $user['id']);
$default = null;
foreach ($addresses as $a) {
    if ((int) $a['is_default'] === 1) {
        $default = $a;
        break;
    }
}
$default = $default ?? ($addresses[0] ?? null);

$old = old_input();

$pageTitle = 'Checkout';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">Checkout</h1>

  <?php if ($preview['coupon_error'] !== null): ?>
    <div style="background:#fef3c7;color:#854d0e;border-left:3px solid #f59e0b;padding:12px 16px;border-radius:6px;margin-bottom:16px;">
      Coupon error: <?= e($preview['coupon_error']) ?>
    </div>
  <?php endif; ?>

  <form action="/store/actions/checkout-place.php" method="post">
    <?php if ($couponCode !== ''): ?>
      <input type="hidden" name="coupon" value="<?= e($couponCode) ?>">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;">
      <div>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;margin-bottom:16px;">
          <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Shipping address</h2>

          <?php if ($addresses !== []): ?>
            <div style="margin-bottom:16px;">
              <label style="display:block;font-size:13px;color:var(--gray-600);margin-bottom:6px;">Use saved address</label>
              <select name="address_id" class="form-input" onchange="this.form.submit_address && this.form.submit_address.click()">
                <option value="0">— Enter manually below —</option>
                <?php foreach ($addresses as $a): ?>
                  <option value="<?= e((string) $a['id']) ?>" <?= ($default && (int) $default['id'] === (int) $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['recipient_name']) ?> — <?= e($a['line1']) ?>, <?= e($a['city']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label">Recipient name *</label>
              <input type="text" class="form-input" name="recipient_name" required value="<?= e($old['recipient_name'] ?? ($default['recipient_name'] ?? $user['name'])) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-input" name="phone" value="<?= e($old['phone'] ?? ($default['phone'] ?? $user['phone'] ?? '')) ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Address line 1 *</label>
            <input type="text" class="form-input" name="line1" required value="<?= e($old['line1'] ?? ($default['line1'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Address line 2</label>
            <input type="text" class="form-input" name="line2" value="<?= e($old['line2'] ?? ($default['line2'] ?? '')) ?>">
          </div>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label">City *</label>
              <input type="text" class="form-input" name="city" required value="<?= e($old['city'] ?? ($default['city'] ?? '')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Postal code</label>
              <input type="text" class="form-input" name="postal_code" value="<?= e($old['postal_code'] ?? ($default['postal_code'] ?? '')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Country *</label>
              <input type="text" class="form-input" name="country" required maxlength="2" value="<?= e($old['country'] ?? ($default['country'] ?? 'US')) ?>">
            </div>
          </div>
          <label style="display:flex;gap:8px;align-items:center;font-size:14px;color:var(--gray-600);margin-top:8px;">
            <input type="checkbox" name="save_address" value="1"> Save this address to my account
          </label>
        </div>

        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;margin-bottom:16px;">
          <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Payment</h2>
          <div style="border:2px solid var(--primary);border-radius:8px;padding:16px;display:flex;align-items:center;gap:12px;background:var(--primary-light);">
            <input type="radio" name="payment_method" value="cod" checked style="accent-color:var(--primary);">
            <div>
              <div style="font-weight:600;"><?= e(t('checkout.cod')) ?></div>
              <div style="font-size:13px;color:var(--gray-500);">Pay when your order arrives.</div>
            </div>
          </div>
        </div>

        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;">
          <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Order notes (optional)</h2>
          <textarea name="notes" class="form-input" rows="3" placeholder="Anything we should know about your delivery?"><?= e($old['notes'] ?? '') ?></textarea>
        </div>
      </div>

      <aside>
        <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;overflow:hidden;position:sticky;top:80px;">
          <div style="padding:16px;background:var(--gray-50);border-bottom:1px solid var(--gray-100);">
            <h2 style="font-size:14px;font-weight:700;margin:0;">Order summary</h2>
          </div>
          <div style="padding:12px 16px;border-bottom:1px solid var(--gray-100);">
            <?php foreach ($activeLines as $line):
              $img = app(ProductImageRepository::class)->byProduct((int) $line['product_id']);
              $imgUrl = $img !== [] ? upload_url((string) $img[0]['path']) : '/store/assets/images/placeholder.svg';
            ?>
              <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--gray-50);">
                <img src="<?= e($imgUrl) ?>" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:6px;background:var(--gray-50);">
                <div style="flex:1;font-size:12px;font-weight:600;line-height:1.3;"><?= e($line['name']) ?> × <?= e((string) $line['qty']) ?></div>
                <div style="font-size:14px;font-weight:700;"><?= e(format_money(\App\Core\Decimal::mul((string) $line['price_snapshot'], (string) $line['qty']))) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div style="padding:16px;">
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0;color:var(--gray-600);">
              <span><?= e(t('cart.subtotal')) ?></span>
              <strong><?= e(format_money($preview['subtotal'])) ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0;color:var(--gray-600);">
              <span><?= e(t('cart.shipping')) ?></span>
              <strong><?= e(format_money($preview['shipping_fee'])) ?></strong>
            </div>
            <?php if (\App\Core\Decimal::comp($preview['discount_total'], '0', 2) > 0): ?>
              <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0;color:var(--success);">
                <span><?= e(t('cart.discount')) ?></span>
                <strong>−<?= e(format_money($preview['discount_total'])) ?></strong>
              </div>
            <?php endif; ?>

            <div style="display:flex;gap:8px;margin:12px 0;">
              <input type="text" name="coupon" class="form-input" placeholder="Promo code" value="<?= e($couponCode) ?>" style="flex:1;">
              <button type="submit" name="action" value="apply_coupon" formaction="/store/pages/checkout/checkout.php" formmethod="get" class="btn btn-outline">Apply</button>
            </div>

            <div style="border-top:2px solid var(--gray-100);margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;font-size:16px;font-weight:800;">
              <span><?= e(t('cart.total')) ?></span>
              <span><?= e(format_money($preview['grand_total'])) ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:16px;">
              <?= e(t('checkout.place_order')) ?>
            </button>
          </div>
        </div>
      </aside>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
