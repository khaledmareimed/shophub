<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\OrderRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/orders/orders-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');
if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'], true)) {
    flash('error', 'Invalid status.');
    redirect('/admin/pages/orders/orders-list.php');
}

$repo = app(OrderRepository::class);
$order = $id > 0 ? $repo->findById($id) : null;
if (!$order) {
    flash('error', 'Order not found.');
    redirect('/admin/pages/orders/orders-list.php');
}

if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
    $repo->cancelOrder($id, 'Cancelled by admin');
} elseif ($status === 'completed') {
    $repo->completeOrder($id);
    $repo->markPaidIfCompleted($id);
} else {
    $repo->updateOrderStatus($id, $status);
}

flash('success', 'Order status updated.');
redirect('/admin/pages/orders/order-details.php?code=' . urlencode((string) $order['code']));
