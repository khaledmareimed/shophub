<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Database;
use App\Repositories\InventoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/orders.php');
}
$user = require_role('customer');

$orderId = (int) ($_POST['order_id'] ?? 0);
$reason = trim((string) ($_POST['reason'] ?? '')) ?: 'Customer cancelled';

$orderRepo = app(OrderRepository::class);
$order = $orderRepo->findById($orderId);
if ($order === null || (int) $order['customer_id'] !== (int) $user['id']) {
    flash('error', 'Order not found.');
    redirect('/store/pages/account/orders.php');
}
if (!in_array($order['status'], ['pending', 'paid', 'processing'], true)) {
    flash('error', 'This order can no longer be cancelled.');
    redirect('/store/pages/account/order-detail.php?code=' . urlencode($order['code']));
}

$pdo = Database::pdo();
$pdo->beginTransaction();
try {
    $items = $orderRepo->items($orderId);
    foreach ($items as $it) {
        $pid = (int) $it['product_id'];
        $qty = (int) $it['qty'];
        app(ProductRepository::class)->adjustStock($pid, $qty);
        app(InventoryRepository::class)->log($pid, $qty, 'order_cancelled', 'order', $orderId);
    }
    $orderRepo->cancelOrder($orderId, $reason);
    $pdo->commit();
    flash('success', 'Order cancelled.');
} catch (\Throwable $e) {
    $pdo->rollBack();
    flash('error', 'Could not cancel order.');
}
redirect('/store/pages/account/order-detail.php?code=' . urlencode($order['code']));
