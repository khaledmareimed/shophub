<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}

$lang = strtolower((string) ($_POST['lang'] ?? 'en'));
if (!in_array($lang, ['en', 'ar'], true)) {
    $lang = 'en';
}
$_SESSION['lang'] = $lang;

$user = current_user();
if ($user) {
    app(UserRepository::class)->updateLocale((int) $user['id'], $lang);
}

$next = (string) ($_POST['next'] ?? '/');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($next === '' || !str_starts_with($next, '/')) {
    $next = '/';
}
redirect($next);
