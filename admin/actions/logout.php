<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/auth/login.php');
}
\App\Web\Guard::logout();
flash('success', t('auth.signed_out'));
redirect('/admin/pages/auth/login.php');
