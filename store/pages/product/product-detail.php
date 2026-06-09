<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CategoryRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;

$slug = trim((string) ($_GET['slug'] ?? ''));
$id = (int) ($_GET['id'] ?? 0);

$products = app(ProductRepository::class);
$product = $slug !== '' ? $products->findBySlug($slug) : ($id > 0 ? $products->findById($id) : null);

if ($product === null || $product['status'] !== 'active') {
    http_response_code(404);
    $pageTitle = 'Product not found';
    ?><!DOCTYPE html>
    <html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
    <head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
    <body>
    <?php require __DIR__ . '/../../includes/topnav.php'; ?>
    <div class="container" style="padding:60px 20px;text-align:center;">
      <h1 style="font-size:28px;margin-bottom:10px;">Product not found</h1>
      <p style="color:var(--gray-500);margin-bottom:24px;">This product may have been removed or is no longer available.</p>
      <a href="/store/pages/catalog/products.php" class="btn btn-primary">Browse all products</a>
    </div>
    <?php require __DIR__ . '/../../includes/footer.php'; ?>
    </body></html>
    <?php
    exit;
}

$products->incrementView((int) $product['id']);

$category = app(CategoryRepository::class)->findById((int) $product['category_id']);
$sellerProfile = app(SellerRepository::class)->findByUserId((int) $product['seller_id']);
$sellerUser = app(UserRepository::class)->findById((int) $product['seller_id']);
$images = app(ProductImageRepository::class)->byProduct((int) $product['id']);
$reviews = app(ReviewRepository::class)->approvedForProduct((int) $product['id']);

$priceCurrent = (string) $product['price'];
$priceCompare = $product['compare_at_price'] !== null ? (string) $product['compare_at_price'] : null;
$discount = null;
if ($priceCompare !== null && (float) $priceCompare > (float) $priceCurrent) {
    $discount = (int) round((1 - ((float) $priceCurrent / (float) $priceCompare)) * 100);
}
$mainImg = $images !== [] ? upload_url((string) $images[0]['path']) : '/store/assets/images/placeholder.svg';
$inStock = (int) $product['stock'] > 0;

$pageTitle = localized($product);
$pageDescription = mb_substr(strip_tags(localized($product, 'description')), 0, 160);

[$related] = $products->searchStores(null, (int) $product['category_id'], 'active', 1, 4, 'newest');
$related = array_values(array_filter($related, static fn ($p) => (int) $p['id'] !== (int) $product['id']));
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:var(--sp-6);">
  <div class="breadcrumb">
    <a href="/store/index.php">Home</a><span class="breadcrumb-sep">/</span>
    <?php if ($category): ?>
      <a href="/store/pages/catalog/products.php?cat=<?= e($category['slug']) ?>"><?= e(localized($category)) ?></a>
      <span class="breadcrumb-sep">/</span>
    <?php endif; ?>
    <span class="breadcrumb-current"><?= e(localized($product)) ?></span>
  </div>

  <div class="product-detail-grid">
    <div>
      <div class="product-gallery-main" id="mainImg">
        <img src="<?= e($mainImg) ?>" alt="<?= e(localized($product)) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
      </div>
      <?php if (count($images) > 1): ?>
        <div class="product-gallery-thumbs">
          <?php foreach ($images as $i => $img):
            $url = upload_url((string) $img['path']);
          ?>
            <div class="thumb-img <?= $i === 0 ? 'active' : '' ?>" onclick="setImg(this,'<?= e($url) ?>')">
              <img src="<?= e($url) ?>" alt="<?= e($img['alt'] ?? '') ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <?php if ($discount !== null): ?>
        <div style="display:flex;gap:var(--sp-2);margin-bottom:var(--sp-3);">
          <span class="badge badge-sale"><?= e($discount) ?>% OFF</span>
        </div>
      <?php endif; ?>
      <h1 class="product-info-title"><?= e(localized($product)) ?></h1>
      <?php if ($sellerProfile): ?>
        <p class="product-info-seller">Sold by <strong><?= e($sellerProfile['business_name']) ?></strong></p>
      <?php elseif ($sellerUser): ?>
        <p class="product-info-seller">Sold by <strong><?= e($sellerUser['name']) ?></strong></p>
      <?php endif; ?>
      <div class="product-info-rating">
        <?= star_row($product['rating_avg'] ?? 0, (int) ($product['rating_count'] ?? 0)) ?>
        <span class="text-muted">·</span>
        <?php if ($inStock): ?>
          <span style="color:var(--success);font-size:var(--text-sm);font-weight:var(--fw-semi);">In Stock (<?= e((string) $product['stock']) ?>)</span>
        <?php else: ?>
          <span style="color:var(--danger);font-size:var(--text-sm);font-weight:var(--fw-semi);">Out of Stock</span>
        <?php endif; ?>
      </div>
      <div style="display:flex;align-items:baseline;gap:var(--sp-3);margin-bottom:var(--sp-4);">
        <span class="product-price-current"><?= e(format_money($priceCurrent)) ?></span>
        <?php if ($priceCompare !== null && (float) $priceCompare > (float) $priceCurrent): ?>
          <span class="product-price-original"><?= e(format_money($priceCompare)) ?></span>
        <?php endif; ?>
        <?php if ($discount !== null): ?>
          <span class="product-price-discount"><?= e($discount) ?>% off</span>
        <?php endif; ?>
      </div>

      <div style="font-size:var(--text-sm);color:var(--gray-600);margin-bottom:var(--sp-4);">
        <?php if (!empty($product['sku'])): ?>SKU: <?= e($product['sku']) ?> &nbsp;|&nbsp;<?php endif; ?>
        <?php if ($category): ?>Category: <?= e(localized($category)) ?><?php endif; ?>
      </div>

      <?php if ($inStock): ?>
        <form method="post" action="/store/actions/cart-add.php" class="product-add-actions" style="display:flex;gap:12px;align-items:stretch;flex-wrap:wrap;">
          <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
          <input type="hidden" name="next" value="/store/pages/cart/cart.php">
          <div class="qty-control" style="flex:0 0 auto;">
            <button type="button" class="qty-btn qty-minus" onclick="document.getElementById('qty').stepDown()">−</button>
            <input class="qty-input" type="number" name="qty" id="qty" value="1" min="1" max="<?= e((string) $product['stock']) ?>">
            <button type="button" class="qty-btn qty-plus" onclick="document.getElementById('qty').stepUp()">+</button>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="flex:1 1 auto;min-width:200px;">Add to Cart</button>
        </form>
        <form method="post" action="/store/actions/wishlist-toggle.php" style="margin-top:8px;">
          <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
          <input type="hidden" name="next" value="<?= e(current_url()) ?>">
          <button type="submit" class="btn btn-outline btn-lg" style="display:inline-flex;gap:8px;align-items:center;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            <span>Wishlist</span>
          </button>
        </form>
      <?php else: ?>
        <button class="btn btn-outline btn-lg" disabled style="width:100%;">Out of stock</button>
      <?php endif; ?>

      <div class="shipping-badges" style="margin-top:var(--sp-5);">
        <div class="shipping-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Cash on delivery</div>
        <div class="shipping-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>30-day return policy</div>
      </div>
    </div>
  </div>

  <div class="tabs" id="reviews">
    <div class="tab-nav">
      <button class="tab-btn active" data-tab="tab-desc">Description</button>
      <button class="tab-btn" data-tab="tab-reviews">Reviews (<?= e((string) count($reviews)) ?>)</button>
    </div>
    <div id="tab-desc" class="tab-panel active">
      <p style="color:var(--gray-700);line-height:1.8;white-space:pre-line;"><?= e(localized($product, 'description')) ?: 'No description provided.' ?></p>
    </div>
    <div id="tab-reviews" class="tab-panel" style="display:none;">
      <?php if ($reviews === []): ?>
        <p style="color:var(--gray-500);">No reviews yet.</p>
      <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
          <div class="review-item">
            <div class="review-item-header">
              <div class="reviewer-avatar">R</div>
              <div>
                <div class="reviewer-name">Verified buyer</div>
                <div class="review-date"><?= e(date('M j, Y', strtotime((string) $rev['created_at']))) ?></div>
              </div>
              <span class="stars" style="margin-left:auto;"><?= str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']) ?></span>
            </div>
            <?php if (!empty($rev['title'])): ?>
              <div class="review-title"><?= e($rev['title']) ?></div>
            <?php endif; ?>
            <?php if (!empty($rev['body'])): ?>
              <div class="review-body"><?= nl2br(e($rev['body'])) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($related !== []): ?>
    <div style="margin-top:var(--sp-10);">
      <h2 class="section-title" style="margin-bottom:var(--sp-5);">You May Also Like</h2>
      <div class="product-grid">
        <?php foreach ($related as $product): ?>
          <?php require __DIR__ . '/../../includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script>
function setImg(el, imgPath){
  document.getElementById('mainImg').innerHTML='<img src="'+imgPath+'" alt="Product image" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">';
  document.querySelectorAll('.thumb-img').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
}
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    e.currentTarget.classList.add('active');
    document.getElementById(e.currentTarget.dataset.tab).style.display = 'block';
  });
});
</script>
</body></html>
