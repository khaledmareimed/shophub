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
        redirect('/store/pages/auth/reset-password.php?token=' . urlencode($token));
    }
    if ($password !== $confirm) {
        flash('error', t('auth.passwords_mismatch'));
        redirect('/store/pages/auth/reset-password.php?token=' . urlencode($token));
    }
    $ok = app(AuthService::class)->resetPassword($token, $password);
    if (!$ok) {
        flash('error', t('auth.reset_invalid'));
        redirect('/store/pages/auth/forgot-password.php');
    }
    flash('success', t('auth.reset_done'));
    redirect('/store/pages/auth/login.php');
}

$pageTitle = 'Reset password';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="auth-page"><div class="auth-card auth-card-narrow">
  <div class="auth-right" style="padding:var(--sp-10);">
    <div style="text-align:center;margin-bottom:var(--sp-6);">
      <h1 class="auth-right-title">Set new password</h1>
      <p class="auth-right-subtitle">Choose a strong password (≥ 8 characters).</p>
    </div>
    <?php if ($token === ''): ?>
      <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;">Missing reset token.</div>
    <?php else: ?>
      <form method="post" action="/store/pages/auth/reset-password.php">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
          <label class="form-label"><?= e(t('auth.password')) ?></label>
          <input type="password" name="password" class="form-input" required minlength="8" autofocus>
        </div>
        <div class="form-group">
          <label class="form-label"><?= e(t('auth.confirm_password')) ?></label>
          <input type="password" name="confirm" class="form-input" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save new password</button>
      </form>
    <?php endif; ?>
    <div class="auth-switch" style="margin-top:var(--sp-5);">
      <a href="/store/pages/auth/login.php">← <?= e(t('common.back')) ?></a>
    </div>
  </div>
</div></div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
