<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

$slug = trim((string) ($_GET['cat'] ?? ''));
if ($slug === '') {
    redirect('/store/pages/catalog/products.php');
}
$qs = http_build_query(['cat' => $slug] + array_intersect_key($_GET, array_flip(['q', 'sort', 'page'])));
redirect('/store/pages/catalog/products.php?' . $qs);
