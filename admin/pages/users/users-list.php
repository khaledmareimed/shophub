<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\UserRepository;

$user = require_role('admin');

$role = (string) ($_GET['role'] ?? 'all');
$status = (string) ($_GET['status'] ?? 'all');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;

[$rows, $total] = app(UserRepository::class)->listPaginated($role, $status, $page, $per);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Users';
$activePage = 'users';
require __DIR__ . '/../../includes/layout-start.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
  <h1 style="font-size:24px;font-weight:700;margin:0;">Users</h1>
  <a href="/admin/actions/users-export.php?role=<?= e($role) ?>&status=<?= e($status) ?>" class="btn btn-outline">Export CSV</a>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;">
    <select name="role" onchange="this.form.submit()" style="padding:8px;border:1px solid var(--gray-300);border-radius:6px;">
      <?php foreach (['all' => 'All roles', 'customer' => 'Customers', 'seller' => 'Sellers', 'admin' => 'Admins'] as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= $role === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()" style="padding:8px;border:1px solid var(--gray-300);border-radius:6px;">
      <?php foreach (['all' => 'All statuses', 'active' => 'Active', 'pending' => 'Pending', 'banned' => 'Banned'] as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<form action="/admin/actions/users-bulk.php" method="post">
  <input type="hidden" name="role" value="<?= e($role) ?>">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <div style="padding:12px 16px;background:var(--gray-50);border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:12px;">
      <select name="bulk_action" style="padding:6px 10px;font-size:13px;border:1px solid var(--gray-300);border-radius:6px;">
        <option value="">— Bulk action —</option>
        <option value="ban">Ban</option>
        <option value="activate">Activate</option>
      </select>
      <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Apply bulk action to selected?');">Apply</button>
      <span style="color:var(--gray-500);font-size:13px;margin-left:auto;"><?= e((string) $total) ?> result(s)</span>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;width:40px;"><input type="checkbox" onclick="this.closest('table').querySelectorAll('input[name=\'ids[]\']').forEach(c => c.checked = this.checked);"></th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">User</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Role</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Status</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Joined</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $u): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;"><input type="checkbox" name="ids[]" value="<?= e((string) $u['id']) ?>"></td>
            <td style="padding:10px 16px;"><strong><?= e($u['name']) ?></strong><br><span style="color:var(--gray-500);font-size:12px;"><?= e($u['email']) ?></span></td>
            <td style="padding:10px 16px;text-transform:capitalize;"><?= e($u['role']) ?></td>
            <td style="padding:10px 16px;"><span style="text-transform:capitalize;color:<?= $u['status'] === 'active' ? '#16a34a' : ($u['status'] === 'banned' ? '#dc2626' : '#f59e0b') ?>;"><?= e($u['status']) ?></span></td>
            <td style="padding:10px 16px;font-size:13px;color:var(--gray-500);"><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
            <td style="padding:10px 16px;text-align:right;">
              <a href="/admin/pages/users/user-details.php?id=<?= e((string) $u['id']) ?>" class="btn btn-outline btn-sm">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if ($totalPages > 1): ?>
  <nav style="display:flex;justify-content:center;gap:8px;margin-top:16px;">
    <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="?role=<?= e($role) ?>&status=<?= e($status) ?>&page=<?= $page - 1 ?>">←</a><?php endif; ?>
    <span style="padding:6px 12px;color:var(--gray-600);">Page <?= $page ?> / <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?><a class="btn btn-outline btn-sm" href="?role=<?= e($role) ?>&status=<?= e($status) ?>&page=<?= $page + 1 ?>">→</a><?php endif; ?>
  </nav>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
