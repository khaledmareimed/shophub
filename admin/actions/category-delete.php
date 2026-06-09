<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Repositories\CategoryRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/pages/categories/categories-list.php');
}
require_role('admin');

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/pages/categories/categories-list.php');
}

try {
    app(CategoryRepository::class)->delete($id);
    flash('success', 'Category deleted.');
} catch (\PDOException $e) {
    flash('error', 'Cannot delete: this category still has products. Re-assign them first.');
}

redirect('/admin/pages/categories/categories-list.php');
