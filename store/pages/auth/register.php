<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Core\Validator;
use App\Services\AuthService;
use App\Web\Flash;
use App\Web\Guard;

if (current_user()) {
    redirect(Guard::homePathFor((string) current_user()['role']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first = trim((string) ($_POST['first_name'] ?? ''));
    $last = trim((string) ($_POST['last_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $agree = isset($_POST['agree']);

    $rules = [
        'first_name' => 'required|max:100',
        'last_name' => 'required|max:100',
        'email' => 'required|email|max:191',
        'password' => 'required|min:8|max:200',
    ];
    $check = Validator::check([
        'first_name' => $first,
        'last_name' => $last,
        'email' => $email,
        'password' => $password,
    ], $rules);

    $errors = $check['errors'];
    if ($password !== $confirm) {
        $errors['confirm'] = 'mismatch';
    }
    if (!$agree) {
        $errors['agree'] = 'required';
    }

    $input = compact('first', 'last', 'email', 'phone');
    if ($errors !== []) {
        Flash::keepInput($input);
        Flash::keepErrors($errors);
        if (isset($errors['confirm'])) {
            flash('error', t('auth.passwords_mismatch'));
        } else {
            flash('error', 'Please correct the errors and try again.');
        }
        redirect('/store/pages/auth/register.php');
    }

    $result = app(AuthService::class)->register([
        'email' => $email,
        'password' => $password,
        'name' => trim($first . ' ' . $last),
        'phone' => $phone !== '' ? $phone : null,
        'locale' => lang(),
        'role' => 'customer',
    ]);
    if (is_array($result)) {
        if (isset($result['email'])) {
            $errors['email'] = $result['email'];
            flash('error', 'That email is already registered.');
        } else {
            flash('error', 'Could not create account.');
        }
        Flash::keepInput($input);
        Flash::keepErrors($errors);
        redirect('/store/pages/auth/register.php');
    }
    $newUser = app(\App\Repositories\UserRepository::class)->findById((int) $result);
    if ($newUser) {
        Guard::login($newUser);
    }
    flash('success', t('auth.account_created'));
    redirect('/store/index.php');
}

$pageTitle = 'Create account';
$old = old_input();
$errs = errors_pull();
?><!DOCTYPE html>
<html lang="<?= e(lang()) ?>" dir="<?= e(dir_attr()) ?>">
<head><?php require __DIR__ . '/../../includes/head.php'; ?></head>
<body>
<?php require __DIR__ . '/../../includes/topnav.php'; ?>
<?php require __DIR__ . '/../../includes/flash.php'; ?>

<div class="auth-page"><div class="auth-card">
  <div class="auth-left">
    <div>
      <div class="auth-logo">
        <div class="auth-logo-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <span>ShopHub</span>
      </div>
      <h2 class="auth-left-title">Join ShopHub Today</h2>
      <p class="auth-left-subtitle">Create your free account and shop from thousands of products across 500+ sellers.</p>
    </div>
    <div class="auth-perks">
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg></div><span>Track all your orders in one place</span></div>
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></div><span>Save products to your wishlist</span></div>
      <div class="auth-perk"><div class="auth-perk-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div><span>Leave reviews &amp; earn rewards</span></div>
    </div>
  </div>
  <div class="auth-right">
    <h1 class="auth-right-title"><?= e(t('auth.register')) ?></h1>
    <p class="auth-right-subtitle">Already have an account? <a href="/store/pages/auth/login.php"><?= e(t('auth.login')) ?></a></p>
    <form method="post" action="/store/pages/auth/register.php" novalidate>
      <div class="form-grid-2">
        <div class="form-group">
          <label class="form-label">First Name</label>
          <input type="text" name="first_name" class="form-input" required value="<?= e($old['first'] ?? '') ?>" placeholder="John">
        </div>
        <div class="form-group">
          <label class="form-label">Last Name</label>
          <input type="text" name="last_name" class="form-input" required value="<?= e($old['last'] ?? '') ?>" placeholder="Smith">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('auth.email')) ?></label>
        <input type="email" name="email" class="form-input" required value="<?= e($old['email'] ?? '') ?>" placeholder="you@example.com">
        <?php if (!empty($errs['email'])): ?><small style="color:#dc2626"><?= e($errs['email']) ?></small><?php endif; ?>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label class="form-label"><?= e(t('auth.password')) ?></label>
          <input type="password" name="password" class="form-input" required minlength="8" placeholder="Min. 8 characters">
        </div>
        <div class="form-group">
          <label class="form-label"><?= e(t('auth.confirm_password')) ?></label>
          <input type="password" name="confirm" class="form-input" required placeholder="Repeat password">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Phone <span style="color:var(--gray-400)">(optional)</span></label>
        <input type="tel" name="phone" class="form-input" value="<?= e($old['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
      </div>
      <div style="display:flex;align-items:flex-start;gap:var(--sp-2);margin-bottom:var(--sp-5);">
        <input type="checkbox" name="agree" id="agreeTerms" style="margin-top:3px;" required>
        <label for="agreeTerms" style="font-size:var(--text-xs);color:var(--gray-600);line-height:1.5;">I agree to the <a href="#" style="color:var(--primary);">Terms of Service</a> and <a href="#" style="color:var(--primary);">Privacy Policy</a></label>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><?= e(t('auth.register')) ?></button>
    </form>
    <div class="auth-switch">Already have an account? <a href="/store/pages/auth/login.php"><?= e(t('auth.login')) ?></a></div>
  </div>
</div></div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
</body></html>
