<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\ProductRepository;
use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;

$slug = trim((string) ($_GET['slug'] ?? ''));
$sellerId = (int) ($_GET['id'] ?? 0);

$sellerRepo = app(SellerRepository::class);
$pdo = \App\Core\Database::pdo();

$profile = null;
if ($slug !== '') {
    $st = $pdo->prepare('SELECT * FROM seller_profiles WHERE slug = ?');
    $st->execute([$slug]);
    $profile = $st->fetch() ?: null;
} elseif ($sellerId > 0) {
    $profile = $sellerRepo->findByUserId($sellerId);
}

if ($profile === null) {
    http_response_code(404);
    $pageTitle = 'Seller not found';
    ?><!DOCTYPE html><html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
    <head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
    <body>
    <?php require __DIR__ . '/../../includes/topnav.php'; ?>
    <div class="container" style="padding:60px 20px;text-align:center;">
      <h1>Seller not found</h1>
      <a href="/store/index.php" class="btn btn-primary">Back to home</a>
    </div>
    <?php require __DIR__ . '/../../includes/footer.php'; ?>
    </body></html>
    <?php
    exit;
}

$user = app(UserRepository::class)->findById((int) $profile['user_id']);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 12;
[$rows, $total] = app(ProductRepository::class)->searchSeller((int) $profile['user_id'], 'active', $page, $per);
$totalPages = max(1, (int) ceil($total / $per));

$pageTitle = (string) $profile['business_name'];
$initials = strtoupper(mb_substr(preg_replace('/\s+/', '', (string) $profile['business_name']), 0, 2));
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-700) 100%);padding:var(--sp-10) 0;">
  <div class="container">
    <div style="display:flex;align-items:center;gap:var(--sp-6);flex-wrap:wrap;">
      <div style="width:90px;height:90px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:var(--fw-black);color:#fff;border:3px solid rgba(255,255,255,0.3);flex-shrink:0;">
        <?= e($initials) ?>
      </div>
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:var(--sp-3);flex-wrap:wrap;margin-bottom:var(--sp-2);">
          <h1 style="color:#fff;font-size:var(--text-3xl);font-weight:var(--fw-black);margin:0;"><?= e($profile['business_name']) ?></h1>
          <?php if ($profile['status'] === 'approved'): ?>
            <span style="background:var(--primary);color:#fff;font-size:var(--text-xs);font-weight:var(--fw-bold);padding:3px 10px;letter-spacing:0.5px;">✓ VERIFIED</span>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:var(--sp-5);flex-wrap:wrap;">
          <span style="color:rgba(255,255,255,0.7);font-size:var(--text-sm);"><?= e((string) $total) ?> products</span>
          <?php if ($user): ?>
            <span style="color:rgba(255,255,255,0.7);font-size:var(--text-sm);">Member since <?= e(date('Y', strtotime((string) $user['created_at']))) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:var(--sp-6);padding-bottom:var(--sp-10);">
  <div class="catalog-header">
    <span class="catalog-count"><strong><?= e((string) $total) ?></strong> products from <?= e($profile['business_name']) ?></span>
  </div>
  <?php if ($rows === []): ?>
    <p style="color:var(--gray-500);text-align:center;padding:40px;">This seller has no listed products yet.</p>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($rows as $product):
        $sellerName = (string) $profile['business_name'];
        require __DIR__ . '/../../includes/product-card.php';
      endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($totalPages > 1): ?>
    <nav style="display:flex;justify-content:center;gap:8px;margin-top:32px;">
      <?php if ($page > 1): ?>
        <a class="btn btn-outline btn-sm" href="?slug=<?= e($slug) ?>&page=<?= $page - 1 ?>">← Previous</a>
      <?php endif; ?>
      <span style="display:inline-flex;align-items:center;padding:0 12px;">Page <?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-outline btn-sm" href="?slug=<?= e($slug) ?>&page=<?= $page + 1 ?>">Next →</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
