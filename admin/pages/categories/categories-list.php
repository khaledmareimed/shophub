<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CategoryRepository;

require_role('admin');

$rows = app(CategoryRepository::class)->allAdmin();
$editing = (int) ($_GET['edit'] ?? 0);
$current = null;
if ($editing > 0) {
    $current = app(CategoryRepository::class)->findById($editing);
}

$pageTitle = 'Categories';
$activePage = 'categories';
require __DIR__ . '/../../includes/layout-start.php';
?>
<h1 style="font-size:24px;font-weight:700;margin:0 0 16px;">Categories</h1>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:var(--gray-50);">
        <tr style="text-align:left;">
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Name</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Slug</th>
          <th style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--gray-500);text-transform:uppercase;">Active</th>
          <th style="padding:10px 16px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $c): ?>
          <tr style="border-top:1px solid var(--gray-100);">
            <td style="padding:10px 16px;"><strong><?= e($c['name']) ?></strong><?php if (!empty($c['name_ar'])): ?><br><span style="color:var(--gray-500);font-size:12px;" dir="rtl"><?= e($c['name_ar']) ?></span><?php endif; ?></td>
            <td style="padding:10px 16px;font-family:monospace;font-size:12px;"><?= e($c['slug']) ?></td>
            <td style="padding:10px 16px;"><?= ((int) $c['active'] === 1) ? 'Yes' : 'No' ?></td>
            <td style="padding:10px 16px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
              <a class="btn btn-outline btn-sm" href="?edit=<?= e((string) $c['id']) ?>">Edit</a>
              <form action="/admin/actions/category-delete.php" method="post" style="margin:0;" onsubmit="return confirm('Delete this category? Existing products in it will not be deleted.');">
                <input type="hidden" name="id" value="<?= e((string) $c['id']) ?>">
                <button class="btn" style="background:#dc2626;color:#fff;font-size:13px;padding:4px 10px;">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <form action="/admin/actions/category-save.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:18px;display:grid;gap:10px;">
    <h2 style="font-size:16px;font-weight:700;margin:0;"><?= $current ? 'Edit category' : 'New category' ?></h2>
    <?php if ($current): ?>
      <input type="hidden" name="id" value="<?= e((string) $current['id']) ?>">
    <?php endif; ?>
    <label style="font-size:13px;">Name (English)<input type="text" name="name" required value="<?= e((string) ($current['name'] ?? '')) ?>" maxlength="120" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Name (Arabic)<input type="text" name="name_ar" dir="rtl" value="<?= e((string) ($current['name_ar'] ?? '')) ?>" maxlength="120" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Slug<input type="text" name="slug" required pattern="[a-z0-9\-]+" value="<?= e((string) ($current['slug'] ?? '')) ?>" maxlength="120" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;">Position<input type="number" name="position" value="<?= e((string) ($current['position'] ?? '0')) ?>" style="width:100%;padding:8px;border:1px solid var(--gray-300);border-radius:6px;"></label>
    <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="active" value="1" <?= (!$current || (int) $current['active'] === 1) ? 'checked' : '' ?>> Active</label>
    <div style="display:flex;gap:6px;">
      <button class="btn btn-primary" type="submit"><?= $current ? 'Save' : 'Create' ?></button>
      <?php if ($current): ?>
        <a href="/admin/pages/categories/categories-list.php" class="btn btn-outline">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout-end.php'; ?>
