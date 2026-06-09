<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\CouponRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/coupons/coupons-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
$code = strtoupper(trim((string) ($_POST['code'] ?? '')));
$type = (string) ($_POST['type'] ?? 'percent');
$value = (string) ($_POST['value'] ?? '0');

if ($code === '') {
    flash('error', 'Code is required.');
    redirect('/admin/pages/coupons/coupons-list.php');
}
if (!in_array($type, ['percent', 'fixed'], true)) {
    flash('error', 'Invalid type.');
    redirect('/admin/pages/coupons/coupons-list.php');
}
if (!is_numeric($value) || (float) $value <= 0) {
    flash('error', 'Value must be positive.');
    redirect('/admin/pages/coupons/coupons-list.php');
}

$expiresAtRaw = (string) ($_POST['expires_at'] ?? '');
$payload = [
    'code' => $code,
    'type' => $type,
    'value' => number_format((float) $value, 2, '.', ''),
    'min_subtotal' => $_POST['min_subtotal'] !== '' ? number_format((float) $_POST['min_subtotal'], 2, '.', '') : null,
    'max_discount' => $_POST['max_discount'] !== '' ? number_format((float) $_POST['max_discount'], 2, '.', '') : null,
    'starts_at' => null,
    'expires_at' => $expiresAtRaw !== '' ? date('Y-m-d H:i:s', strtotime($expiresAtRaw)) : null,
    'usage_limit' => $_POST['usage_limit'] !== '' ? (int) $_POST['usage_limit'] : null,
    'scope' => 'all',
    'scope_id' => null,
    'active' => isset($_POST['active']) ? 1 : 0,
];

$repo = app(CouponRepository::class);
if ($id > 0) {
    if (!$repo->findById($id)) {
        flash('error', 'Coupon not found.');
        redirect('/admin/pages/coupons/coupons-list.php');
    }
    $repo->update($id, $payload);
    flash('success', 'Coupon updated.');
} else {
    try {
        $repo->insert($payload);
        flash('success', 'Coupon created.');
    } catch (\PDOException $e) {
        flash('error', 'Code already exists.');
    }
}
redirect('/admin/pages/coupons/coupons-list.php');
