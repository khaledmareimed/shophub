<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SellerRepository;
use App\Repositories\SettingsRepository;

$user = require_role('seller');
$profile = app(SellerRepository::class)->findByUserId((int) $user['id']);
$payoutSettings = app(SettingsRepository::class)->get('seller_payout:' . (int) $user['id']);

$pdo = \App\Core\Database::pdo();
$st = $pdo->prepare(
    'SELECT COALESCE(SUM(oi.line_total),0)
     FROM order_items oi JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? AND o.status = "completed"'
);
$st->execute([(int) $user['id']]);
$earned = (string) $st->fetchColumn();

$pageTitle = 'Payouts';
$activePage = 'payouts';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Payouts</h1>

<div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;margin-bottom:24px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 12px;">Available balance</h2>
  <div style="font-size:36px;font-weight:800;color:#16a34a;"><?= e(format_money($earned)) ?></div>
  <p style="color:var(--gray-500);font-size:13px;margin-top:8px;">Payouts are issued by the marketplace operator on a monthly cycle.</p>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 12px;">Payout method</h2>
  <?php if ($payoutSettings === []): ?>
    <p style="color:var(--gray-500);font-size:14px;">You haven't configured a payout method yet.</p>
    <a class="btn btn-primary" href="/seller/pages/settings/payment-settings.php">Set payout method</a>
  <?php else: ?>
    <table style="font-size:14px;">
      <?php foreach ($payoutSettings as $k => $v): ?>
        <tr><td style="padding:4px 16px 4px 0;color:var(--gray-500);text-transform:capitalize;"><?= e(str_replace('_', ' ', $k)) ?></td><td style="font-weight:600;"><?= e((string) $v) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <a class="btn btn-outline btn-sm" href="/seller/pages/settings/payment-settings.php" style="margin-top:12px;display:inline-block;">Update method</a>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
