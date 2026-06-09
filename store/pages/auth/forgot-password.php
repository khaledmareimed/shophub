<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\AuthService;

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        app(AuthService::class)->forgotPassword($email);
    }
    flash('success', t('auth.reset_sent'));
    redirect('/store/pages/auth/forgot-password.php?sent=1');
}

$sent = isset($_GET['sent']);
$pageTitle = 'Forgot password';
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="auth-page"><div class="auth-card auth-card-narrow">
  <div class="auth-right" style="padding:var(--sp-10);">
    <div style="text-align:center;margin-bottom:var(--sp-6);">
      <div style="width:64px;height:64px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto var(--sp-4);">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
      </div>
      <h1 class="auth-right-title"><?= e(t('auth.forgot_password')) ?></h1>
      <p class="auth-right-subtitle">Enter your email and we'll send you a reset link.</p>
    </div>
    <?php if ($sent): ?>
      <div style="text-align:center;background:#dcfce7;border-left:3px solid var(--success);padding:var(--sp-4);color:var(--success);font-weight:var(--fw-semi);font-size:var(--text-sm);">
        ✓ <?= e(t('auth.reset_sent')) ?>
      </div>
    <?php else: ?>
      <form method="post" action="/store/pages/auth/forgot-password.php">
        <div class="form-group">
          <label class="form-label" for="email"><?= e(t('auth.email')) ?></label>
          <input type="email" name="email" id="email" class="form-input" required placeholder="you@example.com">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send reset link</button>
      </form>
    <?php endif; ?>
    <div class="auth-switch" style="margin-top:var(--sp-5);">
      <a href="/store/pages/auth/login.php">← <?= e(t('common.back')) ?></a>
    </div>
  </div>
</div></div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
