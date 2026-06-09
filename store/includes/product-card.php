<?php
/**
 * Reusable product card.
 *
 * @var array<string, mixed> $product full product row from products
 * @var string|null $sellerName  optional pre-resolved seller display name
 */
$product = $product ?? null;
if ($product === null) return;
$pid = (int) $product['id'];
$slug = (string) $product['slug'];
$priceCurrent = (string) $product['price'];
$priceCompare = $product['compare_at_price'] !== null ? (string) $product['compare_at_price'] : null;
$discount = null;
if ($priceCompare !== null && (float) $priceCompare > (float) $priceCurrent && (float) $priceCompare > 0) {
    $discount = (int) round((1 - ((float) $priceCurrent / (float) $priceCompare)) * 100);
}
$imgUrl = product_image_url($product);
$pageUrl = '/store/pages/product/product-detail.php?slug=' . urlencode($slug);
?>
<div class="product-card">
  <div class="product-card-img-wrap">
    <a href="<?= e($pageUrl) ?>" class="product-img-placeholder" style="display:block">
      <img src="<?= e($imgUrl) ?>" alt="<?= e(localized($product)) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
    </a>
    <?php if ($discount !== null && $discount > 0): ?>
      <div class="product-card-badges"><span class="badge badge-sale"><?= e($discount) ?>% OFF</span></div>
    <?php elseif (!empty($product['status']) && $product['status'] === 'active' && (int) $product['view_count'] < 50): ?>
      <div class="product-card-badges"><span class="badge badge-new">NEW</span></div>
    <?php endif; ?>
    <form action="/store/actions/wishlist-toggle.php" method="post" style="position:absolute;top:8px;right:8px;margin:0;">
      <input type="hidden" name="product_id" value="<?= e((string) $pid) ?>">
      <input type="hidden" name="next" value="<?= e(current_url()) ?>">
      <button type="submit" class="product-wishlist-btn" aria-label="Toggle wishlist">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
      </button>
    </form>
  </div>
  <div class="product-card-body">
    <?php if (!empty($sellerName)): ?>
      <span class="product-card-seller"><?= e($sellerName) ?></span>
    <?php endif; ?>
    <a href="<?= e($pageUrl) ?>" class="product-card-name"><?= e(localized($product)) ?></a>
    <div class="product-card-rating">
      <?= star_row($product['rating_avg'] ?? 0, (int) ($product['rating_count'] ?? 0)) ?>
    </div>
    <div class="product-card-price">
      <span class="price-current"><?= e(format_money($priceCurrent)) ?></span>
      <?php if ($priceCompare !== null && (float) $priceCompare > (float) $priceCurrent): ?>
        <span class="price-original"><?= e(format_money($priceCompare)) ?></span>
      <?php endif; ?>
      <?php if ($discount !== null && $discount > 0): ?>
        <span class="price-discount"><?= e($discount) ?>% off</span>
      <?php endif; ?>
    </div>
    <?php if ((int) $product['stock'] > 0): ?>
      <form action="/store/actions/cart-add.php" method="post" style="margin:0;">
        <input type="hidden" name="product_id" value="<?= e((string) $pid) ?>">
        <input type="hidden" name="qty" value="1">
        <input type="hidden" name="next" value="<?= e(current_url()) ?>">
        <button type="submit" class="btn btn-primary btn-sm product-card-add">Add to Cart</button>
      </form>
    <?php else: ?>
      <button class="btn btn-outline btn-sm product-card-add" disabled>Out of stock</button>
    <?php endif; ?>
  </div>
</div>
<?php unset($sellerName); ?>
