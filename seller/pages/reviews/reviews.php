<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

$user = require_role('seller');

$pdo = \App\Core\Database::pdo();

$st = $pdo->prepare(
    'SELECT
       AVG(CASE WHEN r.status = "approved" THEN r.rating END) AS avg_rating,
       SUM(CASE WHEN r.status = "approved" THEN 1 ELSE 0 END) AS approved_total,
       SUM(CASE WHEN r.status = "pending" THEN 1 ELSE 0 END) AS pending_total
     FROM reviews r JOIN products p ON p.id = r.product_id
     WHERE p.seller_id = ? AND r.status IN ("pending","approved")'
);
$st->execute([(int) $user['id']]);
$summary = $st->fetch();
$avg = round((float) ($summary['avg_rating'] ?? 0), 2);
$totalApproved = (int) ($summary['approved_total'] ?? 0);
$totalPending = (int) ($summary['pending_total'] ?? 0);

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 20;
$off = ($page - 1) * $per;
$st = $pdo->prepare(
    'SELECT SQL_CALC_FOUND_ROWS r.*, p.name AS product_name, u.name AS customer_name
     FROM reviews r JOIN products p ON p.id = r.product_id JOIN users u ON u.id = r.customer_id
     WHERE p.seller_id = ? AND r.status IN ("pending","approved")
     ORDER BY r.id DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off
);
$st->execute([(int) $user['id']]);
$reviews = $st->fetchAll();
$total = (int) $pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Reviews';
$activePage = 'reviews';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 24px;">Reviews</h1>

<div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
  <div>
    <div style="font-size:42px;font-weight:800;line-height:1;color:var(--primary-color);"><?= e(number_format($avg, 1)) ?></div>
    <div style="font-size:13px;color:var(--gray-500);"><?= e((string) $totalApproved) ?> published<?= $totalPending > 0 ? ' · ' . e((string) $totalPending) . ' pending' : '' ?></div>
  </div>
  <div style="font-size:24px;color:#f59e0b;letter-spacing:2px;"><?= e(str_repeat('★', (int) round($avg)) . str_repeat('☆', 5 - (int) round($avg))) ?></div>
</div>

<?php if ($reviews === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;color:var(--gray-500);">No reviews yet.</div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($reviews as $r): ?>
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
          <div>
            <div style="color:#f59e0b;font-size:18px;letter-spacing:2px;"><?= e(str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating'])) ?></div>
            <strong style="font-size:14px;"><?= e((string) ($r['title'] ?? '')) ?></strong>
            <div style="color:var(--gray-500);font-size:12px;margin-top:2px;">By <?= e($r['customer_name']) ?> on <?= e($r['product_name']) ?></div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
            <?php if (($r['status'] ?? '') === 'pending'): ?>
              <span style="font-size:11px;font-weight:600;color:#b45309;background:#fef3c7;padding:2px 8px;border-radius:999px;">Pending</span>
            <?php endif; ?>
            <div style="font-size:12px;color:var(--gray-500);"><?= e(date('Y-m-d', strtotime((string) $r['created_at']))) ?></div>
          </div>
        </div>
        <?php if (!empty($r['body'])): ?>
          <p style="margin:8px 0 0;font-size:14px;line-height:1.6;color:var(--gray-700);"><?= nl2br(e((string) $r['body'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav style="display:flex;justify-content:center;gap:8px;margin-top:16px;">
      <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="?page=<?= $page - 1 ?>">←</a><?php endif; ?>
      <span style="padding:6px 12px;color:var(--gray-600);">Page <?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="btn btn-outline btn-sm" href="?page=<?= $page + 1 ?>">→</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
