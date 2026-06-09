<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\SettingsRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/settings/shipping-settings.php');
}
$user = require_role('seller');

$flat = (string) ($_POST['flat_fee'] ?? '5.00');
$free = (string) ($_POST['free_threshold'] ?? '50.00');
if (!is_numeric($flat) || !is_numeric($free) || (float) $flat < 0 || (float) $free < 0) {
    flash('error', 'Values must be positive numbers.');
    redirect('/seller/pages/settings/shipping-settings.php');
}

app(SettingsRepository::class)->set('seller_shipping:' . (int) $user['id'], [
    'flat_fee' => number_format((float) $flat, 2, '.', ''),
    'free_threshold' => number_format((float) $free, 2, '.', ''),
]);
flash('success', 'Shipping defaults saved.');
redirect('/seller/pages/settings/shipping-settings.php');
