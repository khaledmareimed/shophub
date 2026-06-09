<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\SettingsRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/settings/site-settings.php');
}
require_role('admin');

$repo = app(SettingsRepository::class);
$repo->set('marketplace', [
    'name' => trim((string) ($_POST['mp_name'] ?? 'Marketplace')),
    'support_email' => trim((string) ($_POST['mp_email'] ?? '')),
    'commission_pct' => number_format((float) ($_POST['mp_commission'] ?? 10), 2, '.', ''),
]);
$repo->set('shipping', [
    'flat_fee' => number_format((float) ($_POST['ship_flat'] ?? 5), 2, '.', ''),
    'free_threshold' => number_format((float) ($_POST['ship_free'] ?? 50), 2, '.', ''),
]);

flash('success', 'Settings saved.');
redirect('/admin/pages/settings/site-settings.php');
