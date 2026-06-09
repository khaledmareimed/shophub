<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\AddressRepository;
use App\Services\CheckoutService;
use App\Web\Flash;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/cart/cart.php');
}
$user = require_role('customer');

$addressId = (int) ($_POST['address_id'] ?? 0);
$saveAddress = isset($_POST['save_address']);

$addressInput = [
    'recipient_name' => trim((string) ($_POST['recipient_name'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
    'line1' => trim((string) ($_POST['line1'] ?? '')),
    'line2' => trim((string) ($_POST['line2'] ?? '')),
    'city' => trim((string) ($_POST['city'] ?? '')),
    'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
    'country' => strtoupper(trim((string) ($_POST['country'] ?? 'US'))),
    'label' => 'Checkout',
];
$couponCode = trim((string) ($_POST['coupon'] ?? '')) ?: null;
$notes = trim((string) ($_POST['notes'] ?? '')) ?: null;

$addressRepo = app(AddressRepository::class);
if ($addressId > 0) {
    $saved = $addressRepo->find($addressId, (int) $user['id']);
    if ($saved !== null) {
        $addressInput = [
            'recipient_name' => (string) $saved['recipient_name'],
            'phone' => (string) ($saved['phone'] ?? ''),
            'line1' => (string) $saved['line1'],
            'line2' => (string) ($saved['line2'] ?? ''),
            'city' => (string) $saved['city'],
            'postal_code' => (string) ($saved['postal_code'] ?? ''),
            'country' => (string) ($saved['country'] ?? 'US'),
            'label' => (string) ($saved['label'] ?? 'Saved address'),
        ];
    }
}

if ($addressInput['recipient_name'] === '' || $addressInput['line1'] === '' || $addressInput['city'] === '' || $addressInput['country'] === '') {
    Flash::keepInput($addressInput + ['notes' => $notes]);
    flash('error', 'Please fill in all required address fields.');
    redirect('/store/pages/checkout/checkout.php' . ($couponCode ? '?coupon=' . urlencode($couponCode) : ''));
}

if ($saveAddress && $addressId === 0) {
    try {
        $addressRepo->insert((int) $user['id'], $addressInput);
    } catch (\Throwable $e) {
        // not fatal — proceed with checkout
    }
}

try {
    $result = app(CheckoutService::class)->place((int) $user['id'], $addressInput, $couponCode, $notes);
    flash('success', 'Order placed successfully!');
    redirect('/store/pages/checkout/order-confirmation.php?code=' . urlencode($result['order_code']));
} catch (\InvalidArgumentException $e) {
    $msg = $e->getMessage();
    if ($msg === 'empty_cart') {
        flash('error', 'Your cart is empty.');
        redirect('/store/pages/cart/cart.php');
    } elseif (str_starts_with($msg, 'out_of_stock:')) {
        flash('error', 'Some items in your cart went out of stock. Please review your cart.');
        redirect('/store/pages/cart/cart.php');
    } else {
        flash('error', 'Could not place order: ' . $msg);
        redirect('/store/pages/checkout/checkout.php');
    }
} catch (\Throwable $e) {
    flash('error', 'Something went wrong placing your order. Please try again.');
    redirect('/store/pages/checkout/checkout.php');
}
