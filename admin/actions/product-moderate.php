<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/products/products-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
$reason = trim((string) ($_POST['reason'] ?? ''));

$repo = app(ProductRepository::class);
$product = $id > 0 ? $repo->findById($id) : null;
if (!$product) {
    flash('error', 'Product not found.');
    redirect('/admin/pages/products/products-list.php');
}

switch ($action) {
    case 'approve':
        $repo->setStatus($id, 'active', null);
        flash('success', 'Product approved.');
        break;
    case 'reject':
        if ($reason === '') {
            flash('error', 'Rejection reason is required.');
            redirect('/admin/pages/products/products-list.php?status=pending');
        }
        $repo->setStatus($id, 'rejected', $reason);
        flash('success', 'Product rejected.');
        break;
    case 'archive':
        $repo->setStatus($id, 'draft', null);
        flash('success', 'Product moved to draft.');
        break;
    default:
        flash('error', 'Invalid action.');
}

redirect('/admin/pages/products/products-list.php?status=' . urlencode((string) ($_POST['return_status'] ?? $product['status'])));
