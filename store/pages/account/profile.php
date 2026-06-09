<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\AddressRepository;

$user = require_role('customer');
$addresses = app(AddressRepository::class)->listForUser((int) $user['id']);

$pageTitle = 'Profile & addresses';
$activePage = 'profile';
$old = old_input();
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:24px;padding-bottom:48px;">
  <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;">
    <?php require __DIR__ . '/../../includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">Profile &amp; addresses</h1>

      <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;margin-bottom:16px;">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:16px;">Personal info</h2>
        <form action="/store/actions/profile-update.php" method="post" novalidate>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-input" required value="<?= e($old['name'] ?? $user['name']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" class="form-input" value="<?= e($old['phone'] ?? ($user['phone'] ?? '')) ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><?= e(t('auth.email')) ?></label>
            <input type="email" class="form-input" value="<?= e($user['email']) ?>" disabled>
            <small style="color:var(--gray-500);">Contact support to change your email.</small>
          </div>
          <button type="submit" class="btn btn-primary"><?= e(t('common.save')) ?></button>
        </form>
      </div>

      <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;margin-bottom:16px;">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:16px;">Change password</h2>
        <form action="/store/actions/password-change.php" method="post" novalidate>
          <div class="form-group">
            <label class="form-label">Current password</label>
            <input type="password" name="current_password" class="form-input" required>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label">New password</label>
              <input type="password" name="new_password" class="form-input" required minlength="8">
            </div>
            <div class="form-group">
              <label class="form-label">Confirm new password</label>
              <input type="password" name="confirm_password" class="form-input" required minlength="8">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Update password</button>
        </form>
      </div>

      <div style="background:#fff;border:1px solid var(--gray-100);border-radius:12px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h2 style="font-size:16px;font-weight:700;margin:0;">Saved addresses</h2>
        </div>

        <?php if ($addresses === []): ?>
          <p style="color:var(--gray-500);font-size:14px;margin-bottom:16px;">No saved addresses yet.</p>
        <?php else: ?>
          <?php foreach ($addresses as $a): ?>
            <div style="border:1px solid var(--gray-100);border-radius:8px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
              <div>
                <?php if ((int) $a['is_default'] === 1): ?>
                  <span style="background:var(--primary-light);color:var(--primary);font-size:11px;font-weight:600;padding:2px 8px;border-radius:99px;text-transform:uppercase;letter-spacing:0.05em;">Default</span>
                <?php endif; ?>
                <div style="font-weight:600;margin-top:4px;"><?= e($a['recipient_name']) ?></div>
                <div style="font-size:13px;color:var(--gray-600);">
                  <?= e($a['line1']) ?><?php if (!empty($a['line2'])): ?>, <?= e($a['line2']) ?><?php endif; ?><br>
                  <?= e($a['city']) ?>, <?= e($a['postal_code']) ?> <?= e($a['country']) ?>
                  <?php if (!empty($a['phone'])): ?><br><?= e($a['phone']) ?><?php endif; ?>
                </div>
              </div>
              <form action="/store/actions/address-delete.php" method="post" style="margin:0;">
                <input type="hidden" name="address_id" value="<?= e((string) $a['id']) ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca;" onclick="return confirm('Delete this address?');">Delete</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <details style="margin-top:8px;">
          <summary style="cursor:pointer;color:var(--primary);font-weight:600;">+ Add a new address</summary>
          <form action="/store/actions/address-create.php" method="post" style="margin-top:12px;background:var(--gray-50);padding:16px;border-radius:8px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div class="form-group">
                <label class="form-label">Recipient name *</label>
                <input type="text" name="recipient_name" class="form-input" required>
              </div>
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-input">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Address line 1 *</label>
              <input type="text" name="line1" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Address line 2</label>
              <input type="text" name="line2" class="form-input">
            </div>
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
              <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-input" required>
              </div>
              <div class="form-group">
                <label class="form-label">Postal</label>
                <input type="text" name="postal_code" class="form-input">
              </div>
              <div class="form-group">
                <label class="form-label">Country *</label>
                <input type="text" name="country" class="form-input" maxlength="2" required value="US">
              </div>
            </div>
            <label style="display:flex;gap:8px;align-items:center;font-size:14px;color:var(--gray-600);margin-bottom:12px;">
              <input type="checkbox" name="is_default" value="1"> Make this my default address
            </label>
            <button type="submit" class="btn btn-primary">Save address</button>
          </form>
        </details>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
