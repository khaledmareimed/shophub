<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;
use App\Web\Flash;
use App\Web\Guard;

if (current_user() && current_user()['role'] === 'admin') {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        flash('error', 'Please fill in all fields.');
        Flash::keepInput(['email' => $email]);
        redirect('/admin/pages/auth/login.php');
    }
    $result = app(AuthService::class)->login($email, $password);
    if (isset($result['code']) || $result['role'] !== 'admin') {
        flash('error', t('auth.invalid_credentials'));
        Flash::keepInput(['email' => $email]);
        redirect('/admin/pages/auth/login.php');
    }
    Guard::login($result);
    flash('success', t('auth.signed_in'));
    redirect('/admin/index.php');
}

$old = old_input();
ob_start();
?>
<form method="post" action="/admin/pages/auth/login.php" novalidate>
  <div class="form-group">
    <label class="form-label" for="email"><?= e(t('auth.email')) ?></label>
    <input type="email" class="form-control" id="email" name="email" required autofocus
           value="<?= e($old['email'] ?? '') ?>" placeholder="admin@multivendor.com">
  </div>
  <div class="form-group">
    <label class="form-label" for="password"><?= e(t('auth.password')) ?></label>
    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
  </div>
  <div class="form-row">
    <span></span>
    <a href="/admin/pages/auth/forgot-password.php" class="link"><?= e(t('auth.forgot_password')) ?></a>
  </div>
  <button type="submit" class="btn btn-primary w-100"><?= e(t('auth.login')) ?></button>
</form>
<?php
$bodyHtml = (string) ob_get_clean();
$title = 'Login';
$heading = 'Admin Portal';
$subheading = 'Sign in to your account';
require __DIR__ . '/../../includes/auth-shell.php';
