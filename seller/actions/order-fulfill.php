<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\OrderRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/orders/orders-list.php');
}
$user = require_role('seller');

$itemId = (int) ($_POST['item_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');
$allowed = ['pending', 'processing', 'shipped', 'delivered'];

if ($itemId <= 0 || !in_array($status, $allowed, true)) {
    flash('error', 'Invalid status.');
    back('/seller/pages/orders/orders-list.php');
}

$repo = app(OrderRepository::class);
$item = $repo->findItem($itemId);
if (!$item || (int) $item['seller_id'] !== (int) $user['id']) {
    flash('error', 'Order item not found.');
    redirect('/seller/pages/orders/orders-list.php');
}

$repo->updateItemFulfillment($itemId, $status);

if ($status === 'delivered') {
    $order = $repo->findById((int) $item['order_id']);
    if ($order && $order['status'] !== 'cancelled') {
        $allItems = $repo->items((int) $order['id']);
        $allDelivered = true;
        foreach ($allItems as $it) {
            if ($it['fulfillment_status'] !== 'delivered') {
                $allDelivered = false;
                break;
            }
        }
        if ($allDelivered) {
            $repo->completeOrder((int) $order['id']);
            $repo->markPaidIfCompleted((int) $order['id']);
        }
    }
}

$order = $repo->findById((int) $item['order_id']);
flash('success', 'Fulfillment status updated.');
redirect('/seller/pages/orders/order-details.php?code=' . urlencode((string) $order['code']));
