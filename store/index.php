<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

$products = app(ProductRepository::class);
[$featured] = $products->searchStores(null, null, 'active', 1, 8, 'newest');
$categories = app(CategoryRepository::class)->allActive();

$pageTitle = 'Discover products from top sellers';
$pageDescription = 'Shop thousands of products from verified sellers.';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/includes/topnav.php'; ?>
<?php require __DIR__ . '/includes/flash.php'; ?>

<section class="store-hero">
  <div class="container">
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-badge">New arrivals every day</div>
        <h1 class="hero-title">Discover Products<br>from <span>Top Sellers</span></h1>
        <p class="hero-subtitle">Shop thousands of products across Electronics, Fashion, Home &amp; more — all in one place from verified sellers.</p>
        <div class="hero-actions">
          <a href="/store/pages/catalog/products.php" class="btn btn-primary btn-lg">Shop Now</a>
          <a href="/seller/pages/auth/register.php" class="btn btn-ghost btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.3);">Become a Seller</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-sm">
  <div class="container">
    <div class="section-header">
      <div><h2 class="section-title">Shop by Category</h2><p class="section-subtitle">Find exactly what you're looking for</p></div>
    </div>
    <?php if ($categories === []): ?>
      <p style="color:var(--gray-500)">No categories yet.</p>
    <?php else: ?>
      <div class="category-grid">
        <?php foreach ($categories as $cat): ?>
          <a href="/store/pages/catalog/category.php?cat=<?= e($cat['slug']) ?>" class="category-card">
            <div class="category-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
            </div>
            <span class="category-name"><?= e(localized($cat)) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="background:#fff;border-top:1px solid var(--gray-100);border-bottom:1px solid var(--gray-100);">
  <div class="container">
    <div class="section-header">
      <div><h2 class="section-title">Featured Products</h2><p class="section-subtitle">Handpicked for you</p></div>
      <a href="/store/pages/catalog/products.php" class="view-all">View All →</a>
    </div>
    <?php if ($featured === []): ?>
      <p style="color:var(--gray-500)">No products yet. Check back soon.</p>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($featured as $product): ?>
          <?php require __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-sm" style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-700) 100%);">
  <div class="container" style="text-align:center;padding-top:3rem;padding-bottom:3rem;">
    <h2 style="color:#fff;font-size:var(--text-3xl);font-weight:var(--fw-black);margin-bottom:var(--sp-3);">Start Selling on ShopHub</h2>
    <p style="color:rgba(255,255,255,0.65);margin-bottom:var(--sp-6);font-size:var(--text-lg);">Join 500+ sellers growing their business on our platform.</p>
    <a href="/seller/pages/auth/register.php" class="btn btn-primary btn-lg">Apply to Sell</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
</body></html>
