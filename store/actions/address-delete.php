<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\AddressRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/profile.php');
}
$user = require_role('customer');

$id = (int) ($_POST['address_id'] ?? 0);
if ($id > 0) {
    app(AddressRepository::class)->delete($id, (int) $user['id']);
    flash('success', 'Address removed.');
}
redirect('/store/pages/account/profile.php');
