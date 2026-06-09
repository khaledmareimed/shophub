<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/orders.php');
}
$user = require_role('customer');

$itemId = (int) ($_POST['order_item_id'] ?? 0);
$productId = (int) ($_POST['product_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));

if ($rating < 1 || $rating > 5) {
    flash('error', 'Rating is required.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/store/pages/account/orders.php');
}

$orderRepo = app(OrderRepository::class);
$item = $orderRepo->findItem($itemId);
if ($item === null || (int) $item['product_id'] !== $productId) {
    flash('error', 'Invalid order item.');
    redirect('/store/pages/account/orders.php');
}
$order = $orderRepo->findById((int) $item['order_id']);
if ($order === null || (int) $order['customer_id'] !== (int) $user['id']) {
    flash('error', 'You can only review your own orders.');
    redirect('/store/pages/account/orders.php');
}
if ($order['status'] !== 'completed') {
    flash('error', 'Reviews are only allowed on completed orders.');
    redirect('/store/pages/account/order-detail.php?code=' . urlencode($order['code']));
}

$reviewRepo = app(ReviewRepository::class);
if ($reviewRepo->findByOrderItem($itemId) !== null) {
    flash('error', 'You already reviewed this item.');
    redirect('/store/pages/account/order-detail.php?code=' . urlencode($order['code']));
}
$reviewRepo->insert([
    'product_id' => $productId,
    'customer_id' => (int) $user['id'],
    'order_item_id' => $itemId,
    'rating' => $rating,
    'title' => $title !== '' ? $title : null,
    'body' => $body !== '' ? $body : null,
    'status' => 'approved',
]);
app(ProductRepository::class)->updateRatingAggregate($productId);
flash('success', 'Thank you for your review.');
redirect('/store/pages/account/order-detail.php?code=' . urlencode($order['code']));
