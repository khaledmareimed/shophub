<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$qs = http_build_query(array_filter(['q' => $q !== '' ? $q : null]));
redirect('/store/pages/catalog/products.php' . ($qs !== '' ? '?' . $qs : ''));
