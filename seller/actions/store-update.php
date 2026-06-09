<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\SellerRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/settings/store-settings.php');
}
$user = require_role('seller');

$businessName = trim((string) ($_POST['business_name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

if ($businessName === '') {
    flash('error', 'Store name is required.');
    redirect('/seller/pages/settings/store-settings.php');
}

app(SellerRepository::class)->updateProfileDetails(
    (int) $user['id'],
    $businessName,
    $description === '' ? null : $description
);
flash('success', 'Store profile updated.');
redirect('/seller/pages/settings/store-settings.php');
