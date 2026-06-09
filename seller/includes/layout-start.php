<?php
/**
 * @var string $pageTitle
 * @var string $activePage one of: dashboard, products, orders, reviews, earnings, payouts, store, shipping, payment
 */
$user = current_user();
$activePage = $activePage ?? 'dashboard';

$navItems = [
    'main' => 'Main Menu',
    ['dashboard', '/seller/index.php', 'Dashboard'],
    'store' => 'My Store',
    ['products', '/seller/pages/products/products-list.php', 'My Products'],
    ['orders', '/seller/pages/orders/orders-list.php', 'My Orders'],
    ['reviews', '/seller/pages/reviews/reviews.php', 'Reviews'],
    'earn' => 'Earnings',
    ['earnings', '/seller/pages/earnings/earnings.php', 'Earnings'],
    ['payouts', '/seller/pages/earnings/payouts.php', 'Payouts'],
    'settings' => 'Settings',
    ['store_settings', '/seller/pages/settings/store-settings.php', 'Store Profile'],
    ['shipping', '/seller/pages/settings/shipping-settings.php', 'Shipping'],
    ['payment', '/seller/pages/settings/payment-settings.php', 'Payout Settings'],
];
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'SellerHub') ?> — SellerHub</title>
  <link rel="stylesheet" href="/seller/css/variables.css">
  <link rel="stylesheet" href="/seller/css/style.css">
  <link rel="stylesheet" href="/seller/css/responsive.css">
</head>
<body>
<div id="app">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>SellerHub</span>
      </div>
    </div>
    <nav class="sidebar-menu">
      <ul class="sidebar-nav">
        <?php foreach ($navItems as $item):
          if (is_string($item)): ?>
            <li class="sidebar-section-title"><?= e($item) ?></li>
          <?php else:
            [$key, $href, $label] = $item;
            $active = $activePage === $key;
          ?>
            <li class="sidebar-nav-item">
              <a href="<?= e($href) ?>" class="sidebar-nav-link <?= $active ? 'active' : '' ?>"><?= e($label) ?></a>
            </li>
          <?php endif;
        endforeach; ?>
      </ul>
    </nav>
    <div class="sidebar-footer">
      <form action="/seller/actions/logout.php" method="post" style="margin:0;">
        <button type="submit" class="btn btn-outline w-100"><?= e(t('auth.logout')) ?></button>
      </form>
    </div>
  </aside>

  <div class="main-content">
    <header class="header">
      <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
      </div>
      <div class="header-right" style="display:flex;align-items:center;gap:12px;">
        <form action="/store/actions/lang.php" method="post" style="margin:0;">
          <input type="hidden" name="next" value="<?= e(current_url()) ?>">
          <button type="submit" name="lang" value="<?= e(lang() === 'ar' ? 'en' : 'ar') ?>" class="btn btn-outline btn-sm" style="font-size:12px;"><?= e(lang() === 'ar' ? 'EN' : 'العربية') ?></button>
        </form>
        <div class="user-profile">
          <div class="user-avatar"><?= e(strtoupper(mb_substr(preg_replace('/\s+/', '', (string) ($user['name'] ?? 'S')) ?: 'S', 0, 2))) ?></div>
          <div class="user-info">
            <span class="user-name"><?= e($user['name'] ?? '') ?></span>
            <span class="user-role">Seller</span>
          </div>
        </div>
      </div>
    </header>

    <main class="content-area">
      <?php foreach (flash_pull() as $m): ?>
        <div class="alert alert-<?= e($m['type'] === 'success' ? 'success' : 'error') ?>" style="padding:12px 14px;margin-bottom:16px;border-radius:6px;font-size:14px;background:<?= $m['type'] === 'success' ? '#dcfce7' : '#fee2e2' ?>;color:<?= $m['type'] === 'success' ? '#166534' : '#991b1b' ?>;border-left:3px solid <?= $m['type'] === 'success' ? '#22c55e' : '#dc2626' ?>;">
          <?= e($m['message']) ?>
        </div>
      <?php endforeach; ?>
