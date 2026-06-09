<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/users/users-list.php');
}
$admin = require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
$repo = app(UserRepository::class);
$user = $id > 0 ? $repo->findById($id) : null;
if (!$user) {
    flash('error', 'User not found.');
    redirect('/admin/pages/users/users-list.php');
}
if ((int) $user['id'] === (int) $admin['id']) {
    flash('error', 'You cannot change your own status.');
    redirect('/admin/pages/users/user-details.php?id=' . $id);
}

$status = match ($action) {
    'ban' => 'banned',
    'activate' => 'active',
    default => null,
};
if ($status === null) {
    flash('error', 'Invalid action.');
    redirect('/admin/pages/users/user-details.php?id=' . $id);
}

$repo->updateStatus($id, $status);
flash('success', 'User status updated.');
redirect('/admin/pages/users/user-details.php?id=' . $id);
