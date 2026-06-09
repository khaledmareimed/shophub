<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;

$user = require_role('seller');
$profile = app(SellerRepository::class)->findByUserId((int) $user['id']) ?? [];

$pageTitle = 'Store profile';
$activePage = 'store_settings';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Store profile</h1>

<form action="/seller/actions/store-update.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Store name *</label>
    <input type="text" name="business_name" required maxlength="255" value="<?= e((string) ($profile['business_name'] ?? '')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Slug (read-only)</label>
    <input type="text" disabled value="<?= e((string) ($profile['slug'] ?? '')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-200);background:var(--gray-100);border-radius:6px;color:var(--gray-500);">
  </div>
  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Account status</label>
    <input type="text" disabled value="<?= e(ucfirst((string) ($profile['status'] ?? 'pending'))) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-200);background:var(--gray-100);border-radius:6px;color:var(--gray-500);text-transform:capitalize;">
  </div>
  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">About your store</label>
    <textarea name="description" rows="6" maxlength="5000" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;"><?= e((string) ($profile['description'] ?? '')) ?></textarea>
  </div>
  <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;border-top:1px solid var(--gray-200);padding-top:16px;">
    <button type="submit" class="btn btn-primary">Save changes</button>
  </div>
</form>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
