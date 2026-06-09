<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;

$admin = require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$target = $id > 0 ? app(UserRepository::class)->findById($id) : null;
if (!$target) {
    flash('error', 'User not found.');
    redirect('/admin/pages/users/users-list.php');
}

$pdo = \App\Core\Database::pdo();
$st = $pdo->prepare('SELECT COUNT(*), COALESCE(SUM(grand_total),0) FROM orders WHERE customer_id = ?');
$st->execute([$id]);
[$ordersCount, $ordersTotal] = $st->fetch(\PDO::FETCH_NUM);

$pageTitle = 'User #' . $id;
$activePage = 'users';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <a href="/admin/pages/users/users-list.php" style="color:var(--gray-500);text-decoration:none;font-size:13px;">← Users</a>
    <h1 style="font-size:24px;font-weight:700;margin:4px 0 0;"><?= e($target['name']) ?></h1>
    <p style="color:var(--gray-500);font-size:14px;margin:4px 0 0;"><?= e($target['email']) ?></p>
  </div>
  <form action="/admin/actions/user-status.php" method="post" style="margin:0;display:flex;gap:8px;">
    <input type="hidden" name="id" value="<?= e((string) $target['id']) ?>">
    <?php if ($target['status'] === 'banned'): ?>
      <button name="action" value="activate" type="submit" class="btn btn-primary">Activate</button>
    <?php else: ?>
      <button name="action" value="ban" type="submit" class="btn" style="background:#dc2626;color:#fff;" onclick="return confirm('Ban this user?');">Ban</button>
    <?php endif; ?>
  </form>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
  <?php foreach ([
    ['Role', ucfirst($target['role'])],
    ['Status', ucfirst($target['status'])],
    ['Locale', strtoupper($target['locale'])],
    ['Phone', $target['phone'] ?? '—'],
    ['Joined', date('Y-m-d', strtotime((string) $target['created_at']))],
    ['Email verified', $target['email_verified_at'] ? 'Yes' : 'No'],
  ] as $row): ?>
    <div style="background:#fff;border:1px solid var(--gray-200);padding:14px;border-radius:6px;">
      <div style="font-size:11px;color:var(--gray-500);text-transform:uppercase;font-weight:600;"><?= e($row[0]) ?></div>
      <div style="font-size:14px;font-weight:600;margin-top:4px;"><?= e((string) ($row[1] ?? '')) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div style="background:#fff;border:1px solid var(--gray-200);padding:18px;border-radius:8px;">
  <h2 style="font-size:16px;font-weight:700;margin:0 0 8px;">Order activity</h2>
  <p style="font-size:14px;color:var(--gray-700);margin:0;">
    <?= e((string) (int) $ordersCount) ?> order(s) · Total <?= e(format_money((string) $ordersTotal)) ?>
  </p>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
