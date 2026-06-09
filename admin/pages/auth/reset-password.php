<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');
    if (strlen($password) < 8) {
        flash('error', 'Password must be at least 8 characters.');
        redirect('/admin/pages/auth/reset-password.php?token=' . urlencode($token));
    }
    if ($password !== $confirm) {
        flash('error', t('auth.passwords_mismatch'));
        redirect('/admin/pages/auth/reset-password.php?token=' . urlencode($token));
    }
    $ok = app(AuthService::class)->resetPassword($token, $password);
    if (!$ok) {
        flash('error', t('auth.reset_invalid'));
        redirect('/admin/pages/auth/forgot-password.php');
    }
    flash('success', t('auth.reset_done'));
    redirect('/admin/pages/auth/login.php');
}

ob_start();
if ($token === ''): ?>
  <div class="alert alert-error">Missing reset token.</div>
<?php else: ?>
  <form method="post" action="/admin/pages/auth/reset-password.php">
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="form-group">
      <label class="form-label"><?= e(t('auth.password')) ?></label>
      <input type="password" name="password" class="form-control" required minlength="8" autofocus>
    </div>
    <div class="form-group">
      <label class="form-label"><?= e(t('auth.confirm_password')) ?></label>
      <input type="password" name="confirm" class="form-control" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary w-100">Save new password</button>
  </form>
<?php endif; ?>
<div style="text-align:center;margin-top:var(--spacing-lg);">
  <a href="/admin/pages/auth/login.php" class="link">&larr; Back to Login</a>
</div>
<?php
$bodyHtml = (string) ob_get_clean();
$title = 'Reset Password';
$heading = 'Set New Password';
$subheading = 'Choose a strong password';
require __DIR__ . '/../../includes/auth-shell.php';
