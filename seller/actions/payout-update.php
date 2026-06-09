<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\SettingsRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/settings/payment-settings.php');
}
$user = require_role('seller');

$method = (string) ($_POST['method'] ?? '');
if (!in_array($method, ['bank', 'paypal', 'check'], true)) {
    flash('error', 'Pick a valid payout method.');
    redirect('/seller/pages/settings/payment-settings.php');
}

app(SettingsRepository::class)->set('seller_payout:' . (int) $user['id'], [
    'method' => $method,
    'account_holder' => trim((string) ($_POST['account_holder'] ?? '')),
    'account_reference' => trim((string) ($_POST['account_reference'] ?? '')),
    'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
]);
flash('success', 'Payout settings saved.');
redirect('/seller/pages/settings/payment-settings.php');
