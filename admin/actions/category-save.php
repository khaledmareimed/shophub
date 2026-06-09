<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\CategoryRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/categories/categories-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
$nameAr = trim((string) ($_POST['name_ar'] ?? ''));
$slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
$position = (int) ($_POST['position'] ?? 0);
$active = isset($_POST['active']) ? 1 : 0;

if ($name === '' || $slug === '') {
    flash('error', 'Name and slug are required.');
    redirect('/admin/pages/categories/categories-list.php');
}
if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    flash('error', 'Slug must be lowercase letters, digits and dashes only.');
    redirect('/admin/pages/categories/categories-list.php');
}

$payload = [
    'parent_id' => null,
    'slug' => $slug,
    'name' => $name,
    'name_ar' => $nameAr === '' ? null : $nameAr,
    'description' => null,
    'image_path' => null,
    'position' => $position,
    'active' => $active,
];

$repo = app(CategoryRepository::class);
if ($id > 0) {
    if (!$repo->findById($id)) {
        flash('error', 'Category not found.');
        redirect('/admin/pages/categories/categories-list.php');
    }
    $repo->update($id, $payload);
    flash('success', 'Category updated.');
} else {
    $repo->insert($payload);
    flash('success', 'Category created.');
}
redirect('/admin/pages/categories/categories-list.php');
