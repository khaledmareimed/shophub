<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\AddressRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/store/pages/account/profile.php');
}
$user = require_role('customer');

$data = [
    'recipient_name' => trim((string) ($_POST['recipient_name'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
    'line1' => trim((string) ($_POST['line1'] ?? '')),
    'line2' => trim((string) ($_POST['line2'] ?? '')),
    'city' => trim((string) ($_POST['city'] ?? '')),
    'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
    'country' => strtoupper(trim((string) ($_POST['country'] ?? 'US'))),
    'is_default' => isset($_POST['is_default']) ? 1 : 0,
    'label' => 'Saved address',
];

if ($data['recipient_name'] === '' || $data['line1'] === '' || $data['city'] === '' || $data['country'] === '') {
    flash('error', 'Please fill in all required fields.');
    redirect('/store/pages/account/profile.php');
}

app(AddressRepository::class)->insert((int) $user['id'], $data);
flash('success', 'Address saved.');
redirect('/store/pages/account/profile.php');
