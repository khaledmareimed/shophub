<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\CouponRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/coupons/coupons-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/pages/coupons/coupons-list.php');
}
app(CouponRepository::class)->delete($id);
flash('success', 'Coupon deleted.');
redirect('/admin/pages/coupons/coupons-list.php');
