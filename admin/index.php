<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Services\ReportService;

$user = require_role('admin');

$pdo = \App\Core\Database::pdo();
$summary = app(ReportService::class)->adminSummary();
$sales = app(ReportService::class)->salesLastDays(14);
$top = app(ReportService::class)->topProducts(5);

$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
$totalSellers = (int) $pdo->query('SELECT COUNT(*) FROM seller_profiles WHERE status = "approved"')->fetchColumn();
$pendingSellers = (int) $pdo->query('SELECT COUNT(*) FROM seller_profiles WHERE status = "pending"')->fetchColumn();
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL')->fetchColumn();
$pendingProducts = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = "pending" AND deleted_at IS NULL')->fetchColumn();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/layout-start.php';
?>
<div class="page-header" style="margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;margin:0;">Dashboard</h1>
  <p style="color:var(--gray-500);margin:4px 0 0;font-size:14px;">Marketplace at a glance.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
  <?php
  $cards = [
      ['Users', $totalUsers, '#3b82f6'],
      ['Approved sellers', $totalSellers, '#16a34a'],
      ['Pending sellers', $pendingSellers, '#f59e0b'],
      ['Products', $totalProducts, '#8b5cf6'],
      ['Pending products', $pendingProducts, '#f59e0b'],
      ['Open revenue', format_money($summary['revenue_all_open']), '#dc2626'],
  ];
  foreach ($cards as [$label, $value, $color]): ?>
    <div style="background:#fff;border:1px solid var(--gray-200);padding:18px;border-radius:8px;">
      <div style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;"><?= e($label) ?></div>
      <div style="font-size:24px;font-weight:800;margin-top:8px;color:<?= $color ?>;"><?= e((string) $value) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:24px;">
  <div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Sales — last 14 days</h2>
    <?php if ($sales === []): ?>
      <p style="color:var(--gray-500);font-size:14px;">No sales yet.</p>
    <?php else:
      $max = 0.0;
      foreach ($sales as $s) $max = max($max, (float) $s['total']);
      $max = max($max, 1.0);
    ?>
      <div style="display:flex;align-items:flex-end;gap:6px;height:160px;">
        <?php foreach ($sales as $s):
          $h = max(2, (int) round(((float) $s['total'] / $max) * 140));
        ?>
          <div style="flex:1;background:linear-gradient(180deg,var(--primary-color),#7c1d1d);height:<?= $h ?>px;border-radius:3px 3px 0 0;" title="<?= e($s['date']) ?>: <?= e(format_money($s['total'])) ?>"></div>
        <?php endforeach; ?>
      </div>
      <script id="chartData" type="application/json"><?= json_encode($sales, JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
  </div>

  <div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Top products</h2>
    <?php if ($top === []): ?>
      <p style="color:var(--gray-500);font-size:14px;">No data yet.</p>
    <?php else: ?>
      <ol style="margin:0;padding-left:20px;font-size:14px;line-height:2;">
        <?php foreach ($top as $t): ?>
          <li><?= e($t['name']) ?> — <strong><?= e((string) $t['qty']) ?></strong></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 12px;">Orders by status</h2>
  <div style="display:flex;flex-wrap:wrap;gap:12px;">
    <?php foreach ($summary['orders_by_status'] ?? [] as $status => $count): ?>
      <div style="border:1px solid var(--gray-200);border-radius:6px;padding:10px 14px;font-size:13px;">
        <span style="text-transform:capitalize;color:var(--gray-500);"><?= e($status) ?></span>
        <strong style="margin-left:8px;"><?= e((string) $count) ?></strong>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/layout-end.php'; ?>
