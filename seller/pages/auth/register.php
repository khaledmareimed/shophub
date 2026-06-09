<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Web\Flash;
use App\Web\Guard;

if (current_user() && current_user()['role'] === 'seller') {
    redirect('/seller/index.php');
}

$showSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim((string) ($_POST['name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');
    $storeName = trim((string) ($_POST['store_name'] ?? ''));
    $storeCategory = trim((string) ($_POST['store_category'] ?? ''));
    $bizType = (string) ($_POST['biz_type'] ?? 'individual');
    $agree = isset($_POST['agree']);

    $errors = [];
    if ($name === '') $errors['name'] = 'required';
    if ($phone === '') $errors['phone'] = 'required';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'email';
    if (strlen($password) < 8) $errors['password'] = 'min:8';
    if ($password !== $confirm) $errors['confirm'] = 'mismatch';
    if ($storeName === '') $errors['store_name'] = 'required';
    if ($storeCategory === '') $errors['store_category'] = 'required';
    if (!in_array($bizType, ['individual', 'company'], true)) $errors['biz_type'] = 'in';
    if (!$agree) $errors['agree'] = 'required';

    $input = compact('name', 'phone', 'email', 'storeName', 'storeCategory', 'bizType');

    if ($errors !== []) {
        Flash::keepInput($input);
        flash('error', isset($errors['confirm']) ? t('auth.passwords_mismatch') : 'Please fill in all required fields.');
        redirect('/seller/pages/auth/register.php');
    }

    $result = app(AuthService::class)->register([
        'email' => $email,
        'password' => $password,
        'name' => $name,
        'phone' => $phone,
        'locale' => lang(),
        'role' => 'seller',
    ]);
    if (is_array($result)) {
        Flash::keepInput($input);
        flash('error', isset($result['email']) ? 'That email is already registered.' : 'Could not submit application.');
        redirect('/seller/pages/auth/register.php');
    }
    $userId = (int) $result;
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($storeName)) ?: 'seller-' . $userId;
    $slug = trim($slug, '-') . '-' . $userId;
    app(SellerRepository::class)->createProfile($userId, $storeName, $slug);
    app(UserRepository::class)->updateStatus($userId, 'pending');
    flash('success', 'Application submitted! We will review and notify you within 24–48 hours.');
    redirect('/seller/pages/auth/register.php?submitted=1');
}

$showSuccess = isset($_GET['submitted']);
$old = old_input();

$extraHeader = '<div class="platform-stats"><span>10K+ Sellers</span><span>$50M+ GMV</span><span>500K+ Customers</span></div>';

ob_start();
if ($showSuccess): ?>
  <div style="text-align:center;padding:var(--spacing-lg) 0;">
    <div style="width:64px;height:64px;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto var(--spacing-lg);color:var(--primary-color);">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <h2 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);color:var(--gray-900);margin-bottom:var(--spacing-sm);">Application Submitted!</h2>
    <p style="font-size:var(--font-size-sm);color:var(--gray-500);line-height:1.6;">Thank you for applying. We'll review your application and notify you within <strong>24–48 hours</strong>.</p>
    <a href="/seller/pages/auth/login.php" class="btn btn-primary" style="margin-top:var(--spacing-lg);display:inline-flex;">Back to Login</a>
  </div>
<?php else: ?>
  <form method="post" action="/seller/pages/auth/register.php" novalidate>
    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required value="<?= e($old['name'] ?? '') ?>" placeholder="John Doe">
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control" required value="<?= e($old['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label"><?= e(t('auth.email')) ?></label>
      <input type="email" name="email" class="form-control" required value="<?= e($old['email'] ?? '') ?>" placeholder="you@yourstore.com">
    </div>
    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label"><?= e(t('auth.password')) ?></label>
        <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min. 8 characters">
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('auth.confirm_password')) ?></label>
        <input type="password" name="confirm" class="form-control" required minlength="8" placeholder="Repeat password">
      </div>
    </div>
    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label">Store Name</label>
        <input type="text" name="store_name" class="form-control" required value="<?= e($old['storeName'] ?? '') ?>" placeholder="My Awesome Store">
      </div>
      <div class="form-group">
        <label class="form-label">Store Category</label>
        <select class="form-control" name="store_category" required>
          <option value="">Select category</option>
          <?php foreach (['Electronics','Fashion','Home & Garden','Gaming','Beauty','Sports','Books','Other'] as $cat): ?>
            <option value="<?= e($cat) ?>" <?= ($old['storeCategory'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Business Type</label>
      <div style="display:flex;gap:var(--spacing-lg);">
        <label style="display:flex;align-items:center;gap:var(--spacing-sm);cursor:pointer;">
          <input type="radio" name="biz_type" value="individual" <?= ($old['bizType'] ?? 'individual') !== 'company' ? 'checked' : '' ?>> Individual
        </label>
        <label style="display:flex;align-items:center;gap:var(--spacing-sm);cursor:pointer;">
          <input type="radio" name="biz_type" value="company" <?= ($old['bizType'] ?? '') === 'company' ? 'checked' : '' ?>> Company / Business
        </label>
      </div>
    </div>
    <div style="display:flex;align-items:flex-start;gap:var(--spacing-sm);margin-bottom:var(--spacing-lg);">
      <input type="checkbox" name="agree" required style="margin-top:2px;">
      <span style="font-size:var(--font-size-sm);color:var(--gray-600);">I agree to the <a href="#" class="link">Terms &amp; Conditions</a> and <a href="#" class="link">Seller Policy</a>.</span>
    </div>
    <button type="submit" class="btn btn-primary w-100">Apply to Sell</button>
    <div style="text-align:center;margin-top:var(--spacing-lg);font-size:var(--font-size-sm);color:var(--gray-600);">
      Already have an account? <a href="/seller/pages/auth/login.php" class="link" style="font-weight:600;">Sign in &rarr;</a>
    </div>
  </form>
<?php endif;
$bodyHtml = (string) ob_get_clean();
$title = 'Apply to Sell';
$heading = 'Join SellerHub';
$subheading = 'Apply to sell on our platform';
$maxWidth = 540;
require __DIR__ . '/../../includes/auth-shell.php';
