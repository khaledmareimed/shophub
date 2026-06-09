<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SellerRepository;

require_role('admin');

$status = (string) ($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;

$result = app(SellerRepository::class)->listForAdmin($status === 'all' ? null : $status, $page, $per);
[$rows, $total] = $result;
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Sellers';
$activePage = 'sellers';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Sellers</h1>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $k => $v): ?>
    <a href="?status=<?= e($k) ?>" class="btn <?= $status === $k ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($v) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($rows === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;color:var(--gray-500);">No sellers found.</div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Store</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Owner</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;"><strong><?= e($r['business_name']) ?></strong><br><span style="color:var(--gray-500);font-size:12px;"><?= e($r['slug']) ?></span></td>
            <td style="padding:10px 16px;"><?= e($r['name']) ?><br><span style="color:var(--gray-500);font-size:12px;"><?= e($r['email']) ?></span></td>
            <td style="padding:10px 16px;text-transform:capitalize;color:<?= $r['status'] === 'approved' ? '#16a34a' : ($r['status'] === 'suspended' ? '#dc2626' : '#f59e0b') ?>;"><?= e($r['status']) ?></td>
            <td style="padding:10px 16px;text-align:right;display:flex;justify-content:flex-end;gap:6px;">
              <?php if ($r['status'] !== 'approved'): ?>
                <form action="/admin/actions/seller-status.php" method="post" style="margin:0;">
                  <input type="hidden" name="user_id" value="<?= e((string) $r['user_id']) ?>">
                  <button name="action" value="approve" class="btn btn-primary btn-sm">Approve</button>
                </form>
              <?php endif; ?>
              <?php if ($r['status'] !== 'suspended'): ?>
                <form action="/admin/actions/seller-status.php" method="post" style="margin:0;" onsubmit="return confirm('Suspend this seller?');">
                  <input type="hidden" name="user_id" value="<?= e((string) $r['user_id']) ?>">
                  <button name="action" value="suspend" class="btn btn-outline btn-sm">Suspend</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav style="display:flex;justify-content:center;gap:8px;margin-top:16px;">
      <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="?status=<?= e($status) ?>&page=<?= $page - 1 ?>">←</a><?php endif; ?>
      <span style="padding:6px 12px;color:var(--gray-600);">Page <?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="btn btn-outline btn-sm" href="?status=<?= e($status) ?>&page=<?= $page + 1 ?>">→</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
