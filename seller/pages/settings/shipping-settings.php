<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SettingsRepository;

$user = require_role('seller');
$key = 'seller_shipping:' . (int) $user['id'];
$settings = app(SettingsRepository::class)->get($key, ['flat_fee' => '5.00', 'free_threshold' => '50.00']);

$pageTitle = 'Shipping';
$activePage = 'shipping';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Shipping</h1>
<p style="color:var(--gray-500);font-size:14px;margin:0 0 16px;">Marketplace-wide shipping is configured by the operator. Use this page to record your default expectations for new orders.</p>

<form action="/seller/actions/shipping-update.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Flat shipping fee</label>
    <input type="number" name="flat_fee" step="0.01" min="0" value="<?= e((string) ($settings['flat_fee'] ?? '5.00')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Free shipping above</label>
    <input type="number" name="free_threshold" step="0.01" min="0" value="<?= e((string) ($settings['free_threshold'] ?? '50.00')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;border-top:1px solid var(--gray-200);padding-top:16px;">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
