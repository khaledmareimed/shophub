<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;
use App\Web\Flash;
use App\Web\Guard;

if (current_user() && current_user()['role'] === 'seller') {
    redirect('/seller/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        flash('error', 'Please fill in all fields.');
        Flash::keepInput(['email' => $email]);
        redirect('/seller/pages/auth/login.php');
    }
    $result = app(AuthService::class)->login($email, $password);
    if (isset($result['code']) || $result['role'] !== 'seller') {
        flash('error', t('auth.invalid_credentials'));
        Flash::keepInput(['email' => $email]);
        redirect('/seller/pages/auth/login.php');
    }
    Guard::login($result);
    flash('success', t('auth.signed_in'));
    redirect('/seller/index.php');
}

$old = old_input();
ob_start();
?>
<form method="post" action="/seller/pages/auth/login.php" novalidate>
  <div class="form-group">
    <label class="form-label" for="email"><?= e(t('auth.email')) ?></label>
    <input type="email" class="form-control" id="email" name="email" required autofocus
           value="<?= e($old['email'] ?? '') ?>" placeholder="you@yourstore.com">
  </div>
  <div class="form-group">
    <label class="form-label" for="password"><?= e(t('auth.password')) ?></label>
    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
  </div>
  <div class="form-row">
    <span></span>
    <a href="/seller/pages/auth/forgot-password.php" class="link"><?= e(t('auth.forgot_password')) ?></a>
  </div>
  <button type="submit" class="btn btn-primary w-100"><?= e(t('auth.login')) ?></button>
</form>
<div style="text-align:center;margin-top:var(--spacing-lg);padding-top:var(--spacing-lg);border-top:1px solid var(--gray-100);font-size:var(--font-size-sm);color:var(--gray-600);">
  New seller? <a href="/seller/pages/auth/register.php" class="link" style="font-weight:600;">Apply to sell here &rarr;</a>
</div>
<?php
$bodyHtml = (string) ob_get_clean();
$title = 'Login';
$heading = 'Seller Portal';
$subheading = 'Sign in to manage your store';
require __DIR__ . '/../../includes/auth-shell.php';
