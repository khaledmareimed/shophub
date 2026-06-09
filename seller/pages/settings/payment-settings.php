<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SettingsRepository;

$user = require_role('seller');
$key = 'seller_payout:' . (int) $user['id'];
$settings = app(SettingsRepository::class)->get($key);

$pageTitle = 'Payout settings';
$activePage = 'payment';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Payout settings</h1>

<form action="/seller/actions/payout-update.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Method *</label>
    <select name="method" required style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
      <?php foreach (['bank' => 'Bank transfer', 'paypal' => 'PayPal', 'check' => 'Check'] as $k => $label): ?>
        <option value="<?= e($k) ?>" <?= ($settings['method'] ?? '') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Account holder</label>
    <input type="text" name="account_holder" maxlength="120" value="<?= e((string) ($settings['account_holder'] ?? '')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Account / email</label>
    <input type="text" name="account_reference" maxlength="120" value="<?= e((string) ($settings['account_reference'] ?? '')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Bank name (if applicable)</label>
    <input type="text" name="bank_name" maxlength="120" value="<?= e((string) ($settings['bank_name'] ?? '')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;border-top:1px solid var(--gray-200);padding-top:16px;">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
