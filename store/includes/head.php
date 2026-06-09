<?php
/**
 * Shared <head> partial.
 * Define $pageTitle and (optionally) $pageDescription before requiring this file.
 *
 * @var string $pageTitle
 * @var string|null $pageDescription
 */
$title = isset($pageTitle) && $pageTitle !== '' ? ($pageTitle . ' — ShopHub') : 'ShopHub';
$desc = $pageDescription ?? 'Shop thousands of products from verified sellers on ShopHub.';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="stylesheet" href="/store/css/variables.css">
<link rel="stylesheet" href="/store/css/store.css">
<link rel="stylesheet" href="/store/css/responsive.css">
