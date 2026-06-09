<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        app(AuthService::class)->forgotPassword($email);
    }
    flash('success', t('auth.reset_sent'));
    redirect('/seller/pages/auth/forgot-password.php?sent=1');
}

$sent = isset($_GET['sent']);
ob_start();
if ($sent): ?>
  <div class="alert alert-success">✓ <?= e(t('auth.reset_sent')) ?></div>
<?php else: ?>
  <form method="post" action="/seller/pages/auth/forgot-password.php">
    <div class="form-group">
      <label class="form-label" for="email"><?= e(t('auth.email')) ?></label>
      <input type="email" name="email" id="email" class="form-control" required autofocus placeholder="you@yourstore.com">
    </div>
    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
  </form>
<?php endif; ?>
<div style="text-align:center;margin-top:var(--spacing-lg);">
  <a href="/seller/pages/auth/login.php" class="link">&larr; Back to Login</a>
</div>
<?php
$bodyHtml = (string) ob_get_clean();
$title = 'Forgot Password';
$heading = 'Reset Password';
$subheading = "We'll send a reset link to your email";
require __DIR__ . '/../../includes/auth-shell.php';
