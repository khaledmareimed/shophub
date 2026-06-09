<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\FileUploader;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/seller/pages/products/products-list.php');
}
$user = require_role('seller');

$productId = (int) ($_POST['product_id'] ?? 0);
$primary = isset($_POST['primary']) && (string) $_POST['primary'] === '1';

$product = $productId > 0 ? app(ProductRepository::class)->findById($productId) : null;
if (!$product || (int) $product['seller_id'] !== (int) $user['id']) {
    flash('error', 'Product not found.');
    redirect('/seller/pages/products/products-list.php');
}

if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    flash('error', 'Please pick an image to upload.');
    redirect('/seller/pages/products/product-edit.php?id=' . $productId);
}

$projectRoot = dirname(__DIR__, 2);
$uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads/products';
$absUpload = str_starts_with($uploadDir, '/')
    ? $uploadDir
    : realpath($projectRoot . '/backend/' . $uploadDir);
if ($absUpload === false) {
    $absUpload = $projectRoot . '/uploads/products';
    if (!is_dir($absUpload)) {
        mkdir($absUpload, 0755, true);
    }
}

try {
    $uploader = new FileUploader();
    $result = $uploader->saveProductImage($_FILES['image'], $absUpload);
    $imageRepo = app(ProductImageRepository::class);
    $existing = $imageRepo->byProduct($productId);
    $position = count($existing);
    $imageRepo->insert($productId, $result['relative'], null, $position, $primary || $existing === []);
    flash('success', 'Image uploaded.');
} catch (\RuntimeException $e) {
    flash('error', 'Upload failed: ' . $e->getMessage());
}

redirect('/seller/pages/products/product-edit.php?id=' . $productId);
