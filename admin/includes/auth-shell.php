<?php
/**
 * @var string $title
 * @var string $heading
 * @var string $subheading
 * @var string $bodyHtml         already-escaped HTML for the inner card body
 */
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> — Multi-Vendor Admin</title>
  <link rel="stylesheet" href="/admin/css/variables.css">
  <link rel="stylesheet" href="/admin/css/style.css">
  <style>
    body { background-color: var(--gray-100); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--spacing-lg); }
    .auth-card { background: #fff; border: 1px solid var(--gray-200); width: 100%; max-width: 460px; box-shadow: var(--shadow-md); }
    .auth-header { background: var(--primary-color); padding: var(--spacing-xl); text-align: center; }
    .auth-header svg { color: #fff; display: block; margin: 0 auto var(--spacing-sm); }
    .auth-header h1 { color: #fff; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); margin: 0; }
    .auth-header p { color: rgba(255, 255, 255, 0.75); font-size: var(--font-size-sm); margin: var(--spacing-xs) 0 0; }
    .auth-body { padding: var(--spacing-xl); }
    .auth-footer-text { text-align: center; padding: var(--spacing-lg); border-top: 1px solid var(--gray-100); font-size: var(--font-size-xs); color: var(--gray-400); }
    .form-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); }
    .link { font-size: var(--font-size-sm); color: var(--primary-color); text-decoration: none; }
    .link:hover { text-decoration: underline; }
    .alert { padding: 12px 14px; margin-bottom: 16px; border-radius: 4px; font-size: var(--font-size-sm); }
    .alert-error { background: #fee2e2; color: #991b1b; border-left: 3px solid #dc2626; }
    .alert-success { background: #dcfce7; color: #166534; border-left: 3px solid var(--success-color); }
  </style>
</head>
<body>
<div class="auth-card">
  <div class="auth-header">
    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
      <rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
    </svg>
    <h1><?= e($heading) ?></h1>
    <p><?= e($subheading) ?></p>
  </div>
  <div class="auth-body">
    <?php foreach (flash_pull() as $m): ?>
      <div class="alert alert-<?= e($m['type'] === 'success' ? 'success' : 'error') ?>"><?= e($m['message']) ?></div>
    <?php endforeach; ?>
    <?= $bodyHtml ?>
  </div>
  <div class="auth-footer-text">&copy; <?= e(date('Y')) ?> Multi-Vendor Ecommerce. All rights reserved.</div>
</div>
</body>
</html>
