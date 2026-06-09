<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

require_role('admin');

$role = (string) ($_GET['role'] ?? 'all');
$status = (string) ($_GET['status'] ?? 'all');

$pdo = \App\Core\Database::pdo();
$where = ['deleted_at IS NULL'];
$params = [];
if ($role !== 'all') {
    $where[] = 'role = ?';
    $params[] = $role;
}
if ($status !== 'all') {
    $where[] = 'status = ?';
    $params[] = $status;
}
$st = $pdo->prepare(
    'SELECT id, email, name, phone, locale, role, status, created_at FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC'
);
$st->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users-' . date('Ymd-His') . '.csv"');
$out = fopen('php://output', 'wb');
fputcsv($out, ['id', 'email', 'name', 'phone', 'locale', 'role', 'status', 'created_at']);
while ($row = $st->fetch()) {
    fputcsv($out, [
        $row['id'], $row['email'], $row['name'], $row['phone'] ?? '',
        $row['locale'], $row['role'], $row['status'], $row['created_at'],
    ]);
}
fclose($out);
exit;
