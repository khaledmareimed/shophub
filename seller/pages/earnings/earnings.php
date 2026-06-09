<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\ReportService;

$user = require_role('seller');
$pdo = \App\Core\Database::pdo();

$st = $pdo->prepare(
    'SELECT
       COALESCE(SUM(CASE WHEN o.status = "completed" THEN oi.line_total ELSE 0 END),0) earned,
       COALESCE(SUM(CASE WHEN o.status IN ("pending","processing") THEN oi.line_total ELSE 0 END),0) upcoming,
       COALESCE(SUM(CASE WHEN o.status = "cancelled" THEN oi.line_total ELSE 0 END),0) cancelled,
       COUNT(*) AS line_count
     FROM order_items oi JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ?'
);
$st->execute([(int) $user['id']]);
$totals = $st->fetch();

$sales = app(ReportService::class)->salesLastDays(30, (int) $user['id']);
$top = app(ReportService::class)->topProducts(5, (int) $user['id']);

$pageTitle = 'Earnings';
$activePage = 'earnings';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Earnings</h1>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Earned</div>
    <div style="font-size:28px;font-weight:800;margin-top:8px;color:#16a34a;"><?= e(format_money((string) $totals['earned'])) ?></div>
    <div style="font-size:11px;color:var(--gray-500);">From completed orders</div>
  </div>
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Upcoming</div>
    <div style="font-size:28px;font-weight:800;margin-top:8px;color:#0ea5e9;"><?= e(format_money((string) $totals['upcoming'])) ?></div>
    <div style="font-size:11px;color:var(--gray-500);">Pending or processing orders</div>
  </div>
  <div style="background:#fff;border:1px solid var(--gray-200);padding:20px;border-radius:8px;">
    <div style="font-size:12px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Cancelled</div>
    <div style="font-size:28px;font-weight:800;margin-top:8px;color:#dc2626;"><?= e(format_money((string) $totals['cancelled'])) ?></div>
  </div>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;margin-bottom:24px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Sales — last 30 days</h2>
  <?php if ($sales === []): ?>
    <p style="color:var(--gray-500);font-size:14px;">No sales yet.</p>
  <?php else:
    $max = 0.0;
    foreach ($sales as $s) $max = max($max, (float) $s['total']);
    $max = max($max, 1.0);
  ?>
    <div style="display:flex;align-items:flex-end;gap:4px;height:160px;">
      <?php foreach ($sales as $s):
        $h = max(2, (int) round(((float) $s['total'] / $max) * 140));
      ?>
        <div style="flex:1;background:linear-gradient(180deg,var(--primary-color),#7c1d1d);height:<?= $h ?>px;border-radius:3px 3px 0 0;" title="<?= e($s['date']) ?>: <?= e(format_money($s['total'])) ?>"></div>
      <?php endforeach; ?>
    </div>
    <script id="salesData" type="application/json"><?= json_encode($sales, JSON_UNESCAPED_SLASHES) ?></script>
  <?php endif; ?>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Top sellers</h2>
  <?php if ($top === []): ?>
    <p style="color:var(--gray-500);font-size:14px;">No data yet.</p>
  <?php else: ?>
    <ol style="margin:0;padding-left:20px;font-size:14px;line-height:2;">
      <?php foreach ($top as $t): ?>
        <li><?= e($t['name']) ?> — <strong><?= e((string) $t['qty']) ?> sold</strong></li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
