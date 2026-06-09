<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CouponRepository;

require_role('admin');

$repo = app(CouponRepository::class);
$rows = $repo->all();
$editing = (int) ($_GET['edit'] ?? 0);
$current = $editing > 0 ? $repo->findById($editing) : null;

$pageTitle = 'Coupons';
$activePage = 'coupons';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Coupons</h1>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Code</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Discount</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Used</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Active</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $c): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;font-family:monospace;font-weight:600;"><?= e($c['code']) ?></td>
            <td style="padding:10px 16px;"><?= $c['type'] === 'percent' ? e($c['value']) . '%' : e(format_money($c['value'])) ?></td>
            <td style="padding:10px 16px;"><?= e((string) $c['used_count']) ?><?php if ($c['usage_limit']): ?> / <?= e((string) $c['usage_limit']) ?><?php endif; ?></td>
            <td style="padding:10px 16px;"><?= ((int) $c['active'] === 1) ? 'Yes' : 'No' ?></td>
            <td style="padding:10px 16px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
              <a class="btn btn-outline btn-sm" href="?edit=<?= e((string) $c['id']) ?>">Edit</a>
              <form action="/admin/actions/coupon-delete.php" method="post" style="margin:0;" onsubmit="return confirm('Delete this coupon?');">
                <input type="hidden" name="id" value="<?= e((string) $c['id']) ?>">
                <button class="btn" style="background:#dc2626;color:#fff;font-size:13px;padding:4px 10px;">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <form action="/admin/actions/coupon-save.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;display:grid;gap:10px;">
    <h2 style="font-size:16px;font-weight:700;margin:0;"><?= $current ? 'Edit coupon' : 'New coupon' ?></h2>
    <?php if ($current): ?>
      <input type="hidden" name="id" value="<?= e((string) $current['id']) ?>">
    <?php endif; ?>
    <label style="font-size:13px;">Code<input type="text" name="code" required value="<?= e((string) ($current['code'] ?? '')) ?>" maxlength="80" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;text-transform:uppercase;"></label>
    <label style="font-size:13px;">Type
      <select name="type" required style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;">
        <option value="percent" <?= ($current['type'] ?? '') === 'percent' ? 'selected' : '' ?>>Percent</option>
        <option value="fixed" <?= ($current['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
      </select>
    </label>
    <label style="font-size:13px;">Value<input type="number" step="0.01" min="0" name="value" required value="<?= e((string) ($current['value'] ?? '')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Min subtotal<input type="number" step="0.01" min="0" name="min_subtotal" value="<?= e((string) ($current['min_subtotal'] ?? '')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Max discount<input type="number" step="0.01" min="0" name="max_discount" value="<?= e((string) ($current['max_discount'] ?? '')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Usage limit<input type="number" min="0" name="usage_limit" value="<?= e((string) ($current['usage_limit'] ?? '')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Expires at<input type="datetime-local" name="expires_at" value="<?= $current && $current['expires_at'] ? e(date('Y-m-d\TH:i', strtotime((string) $current['expires_at']))) : '' ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="active" value="1" <?= (!$current || (int) $current['active'] === 1) ? 'checked' : '' ?>> Active</label>
    <div style="display:flex;gap:6px;">
      <button class="btn btn-primary" type="submit"><?= $current ? 'Save' : 'Create' ?></button>
      <?php if ($current): ?>
        <a href="/admin/pages/coupons/coupons-list.php" class="btn btn-outline">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
