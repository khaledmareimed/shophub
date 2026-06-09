<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Web\Flash;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/profile.php');
}
$user = require_role('customer');

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));

if ($name === '' || strlen($name) > 100) {
    Flash::keepInput(['name' => $name, 'phone' => $phone]);
    flash('error', 'Name is required.');
    redirect('/store/pages/account/profile.php');
}

$pdo = \App\Core\Database::pdo();
$st = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
$st->execute([$name, $phone !== '' ? $phone : null, (int) $user['id']]);

flash('success', 'Profile updated.');
redirect('/store/pages/account/profile.php');
