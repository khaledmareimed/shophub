<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$user = current_user();
if ($user) {
    redirect(\App\Web\Guard::homePathFor((string) $user['role']));
}
redirect('/store/index.php');
