<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Hasher;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/profile.php');
}
$user = require_role('customer');

$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');

$pepper = (string) ($_ENV['JWT_PEPPER'] ?? '');
$pdo = \App\Core\Database::pdo();
$st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$st->execute([(int) $user['id']]);
$row = $st->fetch();

if (!$row || !Hasher::verifyPassword($current, (string) $row['password_hash'], $pepper)) {
    flash('error', 'Current password is incorrect.');
    redirect('/store/pages/account/profile.php');
}
if (strlen($new) < 8) {
    flash('error', 'New password must be at least 8 characters.');
    redirect('/store/pages/account/profile.php');
}
if ($new !== $confirm) {
    flash('error', t('auth.passwords_mismatch'));
    redirect('/store/pages/account/profile.php');
}

$hash = Hasher::hashPassword($new, $pepper);
$pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
    ->execute([$hash, (int) $user['id']]);

flash('success', 'Password updated.');
redirect('/store/pages/account/profile.php');
