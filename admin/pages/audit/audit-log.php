<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

require_role('admin');

$pdo = \App\Core\Database::pdo();
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 50;
$off = ($page - 1) * $per;

$st = $pdo->query(
    'SELECT SQL_CALC_FOUND_ROWS al.*, u.name actor_name, u.email actor_email
     FROM audit_log al LEFT JOIN users u ON u.id = al.actor_id
     ORDER BY al.id DESC LIMIT ' . (int) $per . ' OFFSET ' . (int) $off
);
$rows = $st->fetchAll();
$total = (int) $pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = 'Audit log';
$activePage = 'audit';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Audit log</h1>

<?php if ($rows === []): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:48px 24px;text-align:center;color:var(--gray-500);">No audit events recorded yet.</div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 12px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Time</th>
          <th style="padding:10px 12px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Actor</th>
          <th style="padding:10px 12px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Action</th>
          <th style="padding:10px 12px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Entity</th>
          <th style="padding:10px 12px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:8px 12px;color:var(--gray-500);font-family:monospace;font-size:12px;"><?= e(date('Y-m-d H:i:s', strtotime((string) $r['created_at']))) ?></td>
            <td style="padding:8px 12px;"><?= e((string) ($r['actor_name'] ?? '—')) ?><br><span style="color:var(--gray-500);font-size:11px;text-transform:uppercase;"><?= e($r['actor_role']) ?></span></td>
            <td style="padding:8px 12px;font-family:monospace;font-size:12px;"><?= e($r['action']) ?></td>
            <td style="padding:8px 12px;"><?= e($r['entity']) ?> #<?= e($r['entity_id']) ?></td>
            <td style="padding:8px 12px;color:var(--gray-500);font-family:monospace;font-size:12px;"><?= e((string) ($r['ip'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
