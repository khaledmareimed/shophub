<?php
/**
 * @var array<string, mixed>|null $product Existing product row when editing, null on create.
 * @var list<array<string, mixed>> $categories
 * @var list<array<string, mixed>> $images Product images when editing.
 */
$errs = errors_pull();
$product = $product ?? null;
$isEdit = $product !== null;
$old = function (string $key, mixed $default = '') use ($product) {
    $vals = old_input();
    if (array_key_exists($key, $vals)) {
        return $vals[$key];
    }
    return $product[$key] ?? $default;
};
?>
<form action="/seller/actions/product-save.php" method="post" style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= e((string) $product['id']) ?>">
  <?php endif; ?>

  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Name (English) *</label>
    <input type="text" name="name" required maxlength="255" class="form-input" value="<?= e((string) $old('name')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
    <?php if (isset($errs['name'])): ?><div style="color:#dc2626;font-size:12px;margin-top:4px;"><?= e($errs['name']) ?></div><?php endif; ?>
  </div>

  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Name (Arabic)</label>
    <input type="text" name="name_ar" maxlength="255" class="form-input" value="<?= e((string) $old('name_ar')) ?>" dir="rtl" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Category *</label>
    <select name="category_id" required class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
      <option value="">— Select —</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= e((string) $c['id']) ?>" <?= ((string) $old('category_id')) === (string) $c['id'] ? 'selected' : '' ?>><?= e(localized($c)) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errs['category_id'])): ?><div style="color:#dc2626;font-size:12px;margin-top:4px;"><?= e($errs['category_id']) ?></div><?php endif; ?>
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">SKU</label>
    <input type="text" name="sku" maxlength="80" class="form-input" value="<?= e((string) $old('sku')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Price *</label>
    <input type="number" name="price" step="0.01" min="0" required class="form-input" value="<?= e((string) $old('price')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
    <?php if (isset($errs['price'])): ?><div style="color:#dc2626;font-size:12px;margin-top:4px;"><?= e($errs['price']) ?></div><?php endif; ?>
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Compare-at price</label>
    <input type="number" name="compare_at_price" step="0.01" min="0" class="form-input" value="<?= e((string) $old('compare_at_price')) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Stock *</label>
    <input type="number" name="stock" step="1" min="0" required class="form-input" value="<?= e((string) ($old('stock', '0'))) ?>" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
    <?php if (isset($errs['stock'])): ?><div style="color:#dc2626;font-size:12px;margin-top:4px;"><?= e($errs['stock']) ?></div><?php endif; ?>
  </div>

  <div>
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Status</label>
    <select name="status" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;">
      <?php
      $availableStatuses = $isEdit && in_array($product['status'], ['active', 'rejected', 'outofstock'], true)
          ? ['active' => 'Active', 'draft' => 'Hide (back to draft)']
          : ['draft' => 'Draft', 'pending' => 'Submit for review'];
      $current = (string) $old('status', $isEdit ? (string) $product['status'] : 'draft');
      foreach ($availableStatuses as $k => $label): ?>
        <option value="<?= e($k) ?>" <?= $current === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Description (English)</label>
    <textarea name="description" rows="6" maxlength="20000" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;"><?= e((string) $old('description')) ?></textarea>
  </div>

  <div style="grid-column:1 / -1;">
    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Description (Arabic)</label>
    <textarea name="description_ar" rows="6" maxlength="20000" class="form-input" dir="rtl" style="width:100%;padding:10px 12px;border:1px solid var(--gray-300);border-radius:6px;"><?= e((string) $old('description_ar')) ?></textarea>
  </div>

  <div style="grid-column:1 / -1;display:flex;gap:8px;justify-content:flex-end;border-top:1px solid var(--gray-200);padding-top:16px;">
    <a href="/seller/pages/products/products-list.php" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
  </div>
</form>

<?php if ($isEdit): ?>
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:8px;padding:24px;margin-top:24px;">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 12px;">Images</h2>
    <?php if (($images ?? []) === []): ?>
      <p style="color:var(--gray-500);font-size:14px;">No images yet.</p>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:16px;">
        <?php foreach ($images as $img): ?>
          <div style="position:relative;border:1px solid var(--gray-200);border-radius:6px;overflow:hidden;">
            <img src="<?= e(upload_url((string) $img['path'])) ?>" alt="" style="width:100%;height:140px;object-fit:cover;display:block;">
            <?php if ((int) $img['is_primary'] === 1): ?>
              <span style="position:absolute;top:6px;left:6px;background:var(--primary-color);color:#fff;font-size:10px;padding:2px 8px;border-radius:4px;font-weight:600;">PRIMARY</span>
            <?php endif; ?>
            <form action="/seller/actions/product-image-delete.php" method="post" style="position:absolute;top:6px;right:6px;margin:0;">
              <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
              <input type="hidden" name="image_id" value="<?= e((string) $img['id']) ?>">
              <button type="submit" onclick="return confirm('Remove this image?');" style="border:0;background:rgba(0,0,0,.6);color:#fff;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:14px;line-height:1;">×</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="/seller/actions/product-image-upload.php" method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required style="font-size:14px;">
      <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="primary" value="1"> Set as primary</label>
      <button type="submit" class="btn btn-primary btn-sm">Upload</button>
    </form>
  </div>

  <form action="/seller/actions/product-delete.php" method="post" style="background:#fff;border:1px solid #fecaca;border-radius:8px;padding:24px;margin-top:24px;">
    <input type="hidden" name="id" value="<?= e((string) $product['id']) ?>">
    <h2 style="font-size:16px;font-weight:700;margin:0 0 8px;color:#991b1b;">Delete product</h2>
    <p style="color:var(--gray-600);font-size:13px;margin:0 0 12px;">Once deleted, this product is removed from the storefront. Existing orders are not affected.</p>
    <button type="submit" onclick="return confirm('Delete this product permanently?');" class="btn" style="background:#dc2626;color:#fff;">Delete product</button>
  </form>
<?php endif; ?>
