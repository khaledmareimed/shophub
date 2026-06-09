<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SettingsRepository;

require_role('admin');

$repo = app(SettingsRepository::class);
$shipping = $repo->get('shipping', ['flat_fee' => '5.00', 'free_threshold' => '50.00']);
$marketplace = $repo->get('marketplace', ['name' => 'Marketplace', 'support_email' => '', 'commission_pct' => '10']);

$pageTitle = 'Site settings';
$activePage = 'site';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Site settings</h1>

<form action="/admin/actions/site-settings-save.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <h2 style="grid-column:1 / -1;font-size:16px;font-weight:700;margin:0;">Marketplace</h2>
  <label style="font-size:13px;">Site name<input type="text" name="mp_name" value="<?= e((string) ($marketplace['name'] ?? 'Marketplace')) ?>" maxlength="120" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
  <label style="font-size:13px;">Support email<input type="email" name="mp_email" value="<?= e((string) ($marketplace['support_email'] ?? '')) ?>" maxlength="120" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
  <label style="font-size:13px;">Commission %<input type="number" name="mp_commission" step="0.01" min="0" max="50" value="<?= e((string) ($marketplace['commission_pct'] ?? '10')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>

  <h2 style="grid-column:1 / -1;font-size:16px;font-weight:700;margin:8px 0 0;">Shipping</h2>
  <label style="font-size:13px;">Flat fee<input type="number" name="ship_flat" step="0.01" min="0" value="<?= e((string) ($shipping['flat_fee'] ?? '5.00')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
  <label style="font-size:13px;">Free above<input type="number" name="ship_free" step="0.01" min="0" value="<?= e((string) ($shipping['free_threshold'] ?? '50.00')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>

  <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;border-top:1px solid var(--gray-200);padding-top:16px;">
    <button class="btn btn-primary">Save</button>
  </div>
</form>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
