<?php
/** @var string $activePage one of: dashboard, orders, wishlist, profile */
$user = current_user();
if ($user === null) return;
$activePage = $activePage ?? 'dashboard';
$initials = strtoupper(mb_substr(preg_replace('/\s+/', '', (string) $user['name']) ?: 'U', 0, 2));
?>
<aside class="account-sidebar" style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;overflow:hidden;">
  <div style="padding:24px 20px;background:linear-gradient(135deg,var(--navy) 0%,var(--navy-700) 100%);text-align:center;">
    <div style="width:60px;height:60px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;margin:0 auto 12px;">
      <?= e($initials) ?>
    </div>
    <div style="color:#fff;font-weight:700;"><?= e($user['name']) ?></div>
    <div style="color:rgba(255,255,255,0.6);font-size:12px;margin-top:4px;"><?= e($user['email']) ?></div>
  </div>
  <nav style="padding:12px 0;">
    <?php
    $links = [
      ['dashboard', '/store/pages/account/dashboard.php', 'My dashboard'],
      ['orders', '/store/pages/account/orders.php', 'My orders'],
      ['wishlist', '/store/pages/account/wishlist.php', 'Wishlist'],
      ['profile', '/store/pages/account/profile.php', 'Profile &amp; addresses'],
    ];
    foreach ($links as [$key, $href, $label]):
      $active = $activePage === $key;
    ?>
      <a href="<?= e($href) ?>" style="display:flex;align-items:center;gap:12px;padding:12px 20px;font-size:14px;color:<?= $active ? 'var(--primary)' : 'var(--gray-600)' ?>;text-decoration:none;border-left:3px solid <?= $active ? 'var(--primary)' : 'transparent' ?>;background:<?= $active ? 'var(--primary-light)' : 'transparent' ?>;font-weight:<?= $active ? '600' : '400' ?>;">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
    <hr style="border:0;border-top:1px solid var(--gray-100);margin:8px 0;">
    <form action="/store/actions/logout.php" method="post" style="margin:0;">
      <button type="submit" style="display:flex;align-items:center;gap:12px;padding:12px 20px;font-size:14px;color:#ef4444;background:transparent;border:0;width:100%;text-align:left;cursor:pointer;">
        <?= e(t('auth.logout')) ?>
      </button>
    </form>
  </nav>
</aside>
<?php unset($activePage); ?>
