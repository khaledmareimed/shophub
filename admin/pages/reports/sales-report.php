<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Services\ReportService;

require_role('admin');

$days = max(7, min(180, (int) ($_GET['days'] ?? 30)));
$service = app(ReportService::class);
$summary = $service->adminSummary();
$sales = $service->salesLastDays($days);
$top = $service->topProducts(10);

$pdo = \App\Core\Database::pdo();
$st = $pdo->query(
    'SELECT u.id, u.name, COALESCE(SUM(oi.line_total),0) total, COUNT(oi.id) AS line_count
     FROM users u JOIN order_items oi ON oi.seller_id = u.id
     GROUP BY u.id, u.name ORDER BY total DESC LIMIT 10'
);
$topSellers = $st->fetchAll();

$pageTitle = 'Reports';
$activePage = 'reports';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Reports</h1>

<form method="get" style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
  <label style="font-size:13px;color:var(--gray-600);">Range</label>
  <select name="days" onchange="this.form.submit()" style="padding:6px 10px;border:1px solid var(--gray-300);border-radius:6px;">
    <?php foreach ([7, 14, 30, 60, 90, 180] as $d): ?>
      <option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>>Last <?= $d ?> days</option>
    <?php endforeach; ?>
  </select>
</form>

<div style="background:#fff;border:1px solid var(--gray-200);padding:24px;border-radius:8px;margin-bottom:16px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 16px;">Sales over time</h2>
  <?php if ($sales === []): ?>
    <p style="color:var(--gray-500);font-size:14px;">No sales data.</p>
  <?php else:
    $max = 0.0;
    foreach ($sales as $s) $max = max($max, (float) $s['total']);
    $max = max($max, 1.0);
  ?>
    <div style="display:flex;align-items:flex-end;gap:3px;height:200px;">
      <?php foreach ($sales as $s):
        $h = max(2, (int) round(((float) $s['total'] / $max) * 180));
      ?>
        <div style="flex:1;background:linear-gradient(180deg,var(--primary-color),#7c1d1d);height:<?= $h ?>px;border-radius:2px 2px 0 0;" title="<?= e($s['date']) ?>: <?= e(format_money($s['total'])) ?>"></div>
      <?php endforeach; ?>
    </div>
    <script id="reportSales" type="application/json"><?= json_encode($sales, JSON_UNESCAPED_SLASHES) ?></script>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div style="background:#fff;border:1px solid var(--gray-200);padding:18px;border-radius:8px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 8px;">Top products</h2>
    <?php if ($top === []): ?>
      <p style="color:var(--gray-500);font-size:14px;">No data.</p>
    <?php else: ?>
      <ol style="margin:0;padding-left:20px;font-size:14px;line-height:1.9;">
        <?php foreach ($top as $t): ?>
          <li><?= e($t['name']) ?> — <strong><?= e((string) $t['qty']) ?> sold</strong></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>

  <div style="background:#fff;border:1px solid var(--gray-200);padding:18px;border-radius:8px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 8px;">Top sellers (revenue)</h2>
    <?php if ($topSellers === []): ?>
      <p style="color:var(--gray-500);font-size:14px;">No data.</p>
    <?php else: ?>
      <table style="width:100%;font-size:14px;">
        <?php foreach ($topSellers as $row): ?>
          <tr style="border-bottom:1px solid var(--gray-100);">
            <td style="padding:6px 0;"><?= e($row['name']) ?></td>
            <td style="padding:6px 0;text-align:right;font-weight:600;"><?= e(format_money($row['total'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
