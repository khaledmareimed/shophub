<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Web\Flash;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/products/products-list.php');
}
$user = require_role('seller');

$id = (int) ($_POST['id'] ?? 0);
$repo = app(ProductRepository::class);
$existing = $id > 0 ? $repo->findById($id) : null;
if ($id > 0 && (!$existing || (int) $existing['seller_id'] !== (int) $user['id'])) {
    flash('error', 'Product not found.');
    redirect('/seller/pages/products/products-list.php');
}

$name = trim((string) ($_POST['name'] ?? ''));
$nameAr = trim((string) ($_POST['name_ar'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$sku = trim((string) ($_POST['sku'] ?? ''));
$price = trim((string) ($_POST['price'] ?? ''));
$compare = trim((string) ($_POST['compare_at_price'] ?? ''));
$stock = (int) ($_POST['stock'] ?? 0);
$description = trim((string) ($_POST['description'] ?? ''));
$descriptionAr = trim((string) ($_POST['description_ar'] ?? ''));
$status = (string) ($_POST['status'] ?? 'draft');

$errors = [];
if ($name === '' || mb_strlen($name) < 3) {
    $errors['name'] = 'Name must be at least 3 characters.';
}
if ($categoryId <= 0 || !app(CategoryRepository::class)->findById($categoryId)) {
    $errors['category_id'] = 'Pick a valid category.';
}
if (!is_numeric($price) || (float) $price < 0) {
    $errors['price'] = 'Price must be a non-negative number.';
}
if ($compare !== '' && (!is_numeric($compare) || (float) $compare < 0)) {
    $errors['compare_at_price'] = 'Compare-at price must be a positive number.';
}
if ($stock < 0) {
    $errors['stock'] = 'Stock cannot be negative.';
}

$allowedStatuses = $existing && in_array($existing['status'], ['active', 'rejected', 'outofstock'], true)
    ? ['active', 'draft']
    : ['draft', 'pending'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = $allowedStatuses[0];
}

if ($errors) {
    Flash::keepInput($_POST);
    Flash::keepErrors($errors);
    flash('error', 'Please fix the errors below.');
    redirect($id > 0 ? '/seller/pages/products/product-edit.php?id=' . $id : '/seller/pages/products/product-create.php');
}

$slugBase = preg_replace('/[^a-z0-9]+/', '-', strtolower($name)) ?: 'product';
$slug = trim($slugBase, '-');

$payload = [
    'seller_id' => (int) $user['id'],
    'category_id' => $categoryId,
    'slug' => $slug . '-' . ($id > 0 ? $id : substr(bin2hex(random_bytes(4)), 0, 6)),
    'name' => $name,
    'name_ar' => $nameAr === '' ? null : $nameAr,
    'description' => $description === '' ? null : $description,
    'description_ar' => $descriptionAr === '' ? null : $descriptionAr,
    'price' => number_format((float) $price, 2, '.', ''),
    'compare_at_price' => $compare === '' ? null : number_format((float) $compare, 2, '.', ''),
    'sku' => $sku === '' ? null : $sku,
    'stock' => $stock,
    'status' => $status,
];

if ($id > 0) {
    $payload['slug'] = $existing['slug'];
    $payload['rejection_reason'] = $status === 'pending' ? null : $existing['rejection_reason'];
    $repo->update($id, $payload);
    flash('success', 'Product updated.');
    redirect('/seller/pages/products/product-edit.php?id=' . $id);
}

$newId = $repo->insert($payload);
flash('success', 'Product created.');
redirect('/seller/pages/products/product-edit.php?id=' . $newId);
