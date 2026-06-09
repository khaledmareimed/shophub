<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

$q = trim((string) ($_GET['q'] ?? ''));
$catSlug = trim((string) ($_GET['cat'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'newest');
if (!in_array($sort, ['newest', 'price_asc', 'price_desc'], true)) {
    $sort = 'newest';
}
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 12;

$catRepo = app(CategoryRepository::class);
$category = $catSlug !== '' ? $catRepo->findBySlug($catSlug) : null;
$catId = $category ? (int) $category['id'] : null;

[$rows, $total] = app(ProductRepository::class)->searchStores(
    $q !== '' ? $q : null,
    $catId,
    'active',
    $page,
    $per,
    $sort
);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = $category ? localized($category) : ($q !== '' ? "Search: $q" : 'All Products');
$qsBase = http_build_query(array_filter([
    'q' => $q !== '' ? $q : null,
    'cat' => $catSlug !== '' ? $catSlug : null,
    'sort' => $sort !== 'newest' ? $sort : null,
]));
$linkFor = static function (array $overrides) use ($q, $catSlug, $sort): string {
    $params = array_filter(array_merge([
        'q' => $q !== '' ? $q : null,
        'cat' => $catSlug !== '' ? $catSlug : null,
        'sort' => $sort,
    ], $overrides), static fn ($v) => $v !== null && $v !== '');
    $qs = http_build_query($params);
    return '/store/pages/catalog/products.php' . ($qs !== '' ? '?' . $qs : '');
};
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="container" style="padding-top:var(--sp-6);">
  <div class="breadcrumb">
    <a href="/store/index.php">Home</a><span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current"><?= e($pageTitle) ?></span>
  </div>

  <div class="catalog-layout">
    <aside class="filter-sidebar">
      <form method="get" action="/store/pages/catalog/products.php">
        <input type="hidden" name="q" value="<?= e($q) ?>">
        <div class="filter-header">
          <span class="filter-title">Filters</span>
          <a href="/store/pages/catalog/products.php" class="filter-clear">Clear all</a>
        </div>
        <div class="filter-group">
          <div class="filter-group-title">Category</div>
          <?php foreach ($catRepo->allActive() as $c): ?>
            <label class="filter-option">
              <input type="radio" name="cat" value="<?= e($c['slug']) ?>" <?= $catSlug === $c['slug'] ? 'checked' : '' ?>>
              <?= e(localized($c)) ?>
            </label>
          <?php endforeach; ?>
          <label class="filter-option">
            <input type="radio" name="cat" value="" <?= $catSlug === '' ? 'checked' : '' ?>>
            All categories
          </label>
        </div>
        <div class="filter-group">
          <div class="filter-group-title">Sort</div>
          <select name="sort" class="form-input">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest first</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
      </form>
    </aside>

    <main>
      <div class="catalog-header">
        <span class="catalog-count">
          <?php if ($total === 0): ?>
            No products
          <?php else: ?>
            Showing <strong><?= e(min(($page - 1) * $per + 1, $total)) ?>–<?= e(min($page * $per, $total)) ?></strong> of <?= e((string) $total) ?> products
          <?php endif; ?>
        </span>
      </div>

      <?php if ($rows === []): ?>
        <div style="text-align:center;padding:60px 20px;background:#fff;border:1px solid var(--gray-100);border-radius:8px;">
          <p style="color:var(--gray-500);">No products match your filters.</p>
          <a href="/store/pages/catalog/products.php" class="btn btn-outline" style="margin-top:16px;">Clear filters</a>
        </div>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($rows as $product): ?>
            <?php require __DIR__ . '/../../includes/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($totalPages > 1): ?>
        <nav class="pagination" style="display:flex;justify-content:center;gap:8px;margin-top:32px;">
          <?php if ($page > 1): ?>
            <a class="btn btn-outline btn-sm" href="<?= e($linkFor(['page' => $page - 1])) ?>">← Previous</a>
          <?php endif; ?>
          <span style="display:inline-flex;align-items:center;padding:0 12px;color:var(--gray-600);">
            Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?>
          </span>
          <?php if ($page < $totalPages): ?>
            <a class="btn btn-outline btn-sm" href="<?= e($linkFor(['page' => $page + 1])) ?>">Next →</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </main>
  </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
