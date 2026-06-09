<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;
use App\Web\Guard;

if (current_user()) {
    redirect(Guard::homePathFor((string) current_user()['role']));
}

$nextRaw = (string) ($_GET['next'] ?? $_POST['next'] ?? '/store/index.php');
$nextSafe = (str_starts_with($nextRaw, '/') && !str_starts_with($nextRaw, '//')) ? $nextRaw : '/store/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        flash('error', 'Please fill in all fields.');
        \App\Web\Flash::keepInput(['email' => $email]);
        redirect('/store/pages/auth/login.php?next=' . urlencode($nextSafe));
    }
    $result = app(AuthService::class)->login($email, $password);
    if (isset($result['code'])) {
        flash('error', match ($result['code']) {
            'banned' => t('auth.account_banned'),
            default => t('auth.invalid_credentials'),
        });
        \App\Web\Flash::keepInput(['email' => $email]);
        redirect('/store/pages/auth/login.php?next=' . urlencode($nextSafe));
    }
    if ($result['role'] !== 'customer') {
        flash('error', t('auth.invalid_credentials'));
        redirect('/store/pages/auth/login.php');
    }
    Guard::login($result);
    flash('success', t('auth.signed_in'));
    redirect($nextSafe);
}

$pageTitle = 'Sign in';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="auth-page"><div class="auth-card">
  <div class="auth-left">
    <div>
      <div class="auth-logo">
        <div class="auth-logo-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <span>ShopHub</span>
      </div>
      <h2 class="auth-left-title">Welcome back!</h2>
      <p class="auth-left-subtitle">Sign in to track orders, manage your wishlist and enjoy a personalised experience.</p>
    </div>
    <div class="auth-perks">
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg></div><span>Free shipping on orders over $50</span></div>
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10M1 14l5.37 4.36A9 9 0 0 0 20.49 15"></path></svg></div><span>Easy 30-day returns</span></div>
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div><span>Shop from 500+ verified sellers</span></div>
    </div>
  </div>
  <div class="auth-right">
    <h1 class="auth-right-title"><?= e(t('auth.login')) ?></h1>
    <p class="auth-right-subtitle">Don't have an account? <a href="/store/pages/auth/register.php">Create one free →</a></p>
    <form method="post" action="/store/pages/auth/login.php" novalidate>
      <input type="hidden" name="next" value="<?= e($nextSafe) ?>">
      <div class="form-group">
        <label class="form-label" for="email"><?= e(t('auth.email')) ?></label>
        <input type="email" class="form-input" id="email" name="email" required autofocus value="<?= e(old('email', '')) ?>" placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label class="form-label" for="password"><?= e(t('auth.password')) ?></label>
        <input type="password" class="form-input" id="password" name="password" required placeholder="Your password">
        <div style="text-align:right;margin-top:6px;">
          <a href="/store/pages/auth/forgot-password.php" style="font-size:var(--text-xs);color:var(--primary);"><?= e(t('auth.forgot_password')) ?></a>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><?= e(t('auth.login')) ?></button>
    </form>
    <div class="auth-switch" style="margin-top:var(--sp-5);">New to ShopHub? <a href="/store/pages/auth/register.php"><?= e(t('auth.register')) ?></a></div>
  </div>
</div></div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
