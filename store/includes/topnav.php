<?php
/** @var array<int, array{type:string,message:string}>|null $flashItems */
$user = current_user();
?>
<nav class="topnav"><div class="container"><div class="topnav-inner">
  <a href="/store/index.php" class="topnav-logo">
    <div class="topnav-logo-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
      </svg>
    </div>
    <span>ShopHub</span>
  </a>
  <form class="topnav-search" action="/store/pages/search/search-results.php" method="get" role="search">
    <input type="text" name="q" id="searchInput" placeholder="Search products, brands, categories..." value="<?= e($_GET['q'] ?? '') ?>">
    <button type="submit" class="topnav-search-btn" id="searchBtn">Search</button>
  </form>
  <div class="topnav-actions">
    <a href="/store/pages/account/wishlist.php" class="nav-icon-btn">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
      <span class="nav-icon-label"><?= e(t('nav.wishlist')) ?></span>
    </a>
    <a href="/store/pages/cart/cart.php" class="nav-icon-btn">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span class="nav-icon-label"><?= e(t('nav.cart')) ?></span>
    </a>
    <form action="/store/actions/lang.php" method="post" style="display:inline-flex;align-items:center;margin:0;">
      <input type="hidden" name="next" value="<?= e(current_url()) ?>">
      <button type="submit" name="lang" value="<?= e(lang() === 'ar' ? 'en' : 'ar') ?>" class="nav-link-btn" style="border:0;background:transparent;cursor:pointer;font-weight:600;">
        <?= e(lang() === 'ar' ? 'EN' : 'العربية') ?>
      </button>
    </form>
    <div id="authNav">
      <?php if ($user && $user['role'] === 'customer'): ?>
        <a href="/store/pages/account/dashboard.php" class="nav-link-btn"><?= e($user['name']) ?></a>
        <form action="/store/actions/logout.php" method="post" style="display:inline">
          <button type="submit" class="nav-link-btn" style="border:0;background:transparent;cursor:pointer;"><?= e(t('auth.logout')) ?></button>
        </form>
      <?php else: ?>
        <a href="/store/pages/auth/login.php" class="nav-link-btn"><?= e(t('auth.login')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</div></div></nav>
<nav class="topnav-secondary"><div class="container">
  <ul class="category-nav">
    <li><a href="/store/pages/catalog/products.php" class="category-nav-link">All Products</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=electronics" class="category-nav-link">Electronics</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=fashion" class="category-nav-link">Fashion</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=home" class="category-nav-link">Home &amp; Garden</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=gaming" class="category-nav-link">Gaming</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=beauty" class="category-nav-link">Beauty</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=sports" class="category-nav-link">Sports</a></li>
    <li><a href="/store/pages/catalog/category.php?cat=books" class="category-nav-link">Books</a></li>
  </ul>
</div></nav>
