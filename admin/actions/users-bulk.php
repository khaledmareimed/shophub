<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/users/users-list.php');
}
$admin = require_role('admin');

$ids = $_POST['ids'] ?? [];
$action = (string) ($_POST['bulk_action'] ?? '');
$role = (string) ($_POST['role'] ?? 'all');
$status = (string) ($_POST['status'] ?? 'all');

if (!is_array($ids) || $ids === []) {
    flash('error', 'No users selected.');
    redirect('/admin/pages/users/users-list.php?role=' . urlencode($role) . '&status=' . urlencode($status));
}

$newStatus = match ($action) {
    'ban' => 'banned',
    'activate' => 'active',
    default => null,
};
if ($newStatus === null) {
    flash('error', 'Pick a bulk action.');
    redirect('/admin/pages/users/users-list.php?role=' . urlencode($role) . '&status=' . urlencode($status));
}

$repo = app(UserRepository::class);
$updated = 0;
foreach ($ids as $rawId) {
    $id = (int) $rawId;
    if ($id <= 0 || $id === (int) $admin['id']) {
        continue;
    }
    $repo->updateStatus($id, $newStatus);
    $updated++;
}

flash('success', "Updated $updated user(s).");
redirect('/admin/pages/users/users-list.php?role=' . urlencode($role) . '&status=' . urlencode($status));
