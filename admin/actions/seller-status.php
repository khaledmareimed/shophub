<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/sellers/sellers-list.php');
}
$admin = require_role('admin');

$userId = (int) ($_POST['user_id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');

$sellerRepo = app(SellerRepository::class);
$userRepo = app(UserRepository::class);
$profile = $userId > 0 ? $sellerRepo->findByUserId($userId) : null;
if (!$profile) {
    flash('error', 'Seller not found.');
    redirect('/admin/pages/sellers/sellers-list.php');
}

switch ($action) {
    case 'approve':
        $sellerRepo->updateStatus($userId, 'approved', (int) $admin['id']);
        $userRepo->updateStatus($userId, 'active');
        flash('success', 'Seller approved.');
        break;
    case 'suspend':
        $sellerRepo->updateStatus($userId, 'suspended', null);
        $userRepo->updateStatus($userId, 'banned');
        flash('success', 'Seller suspended.');
        break;
    default:
        flash('error', 'Invalid action.');
}

redirect('/admin/pages/sellers/sellers-list.php');
