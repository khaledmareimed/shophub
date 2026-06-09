<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$backendRoot = $projectRoot . '/backend';

require $backendRoot . '/autoload.php';
require $backendRoot . '/src/Web/Helpers.php';
require __DIR__ . '/../functions/initialize-guard.php';
require __DIR__ . '/../functions/run-initialize.php';

use App\Core\Env;
use App\Services\ComposerInstaller;
use App\Services\MigrationRunner;
use App\Web\Session;

Env::load($backendRoot . '/.env');
Session::start();

$dbCfg = require $backendRoot . '/config/database.php';
$composer = new ComposerInstaller($backendRoot);
$vendorReady = $composer->isInstalled();
$runner = new MigrationRunner($backendRoot . '/database/migrations', $dbCfg);

$connError = $runner->testConnection();
$pending = $connError === null ? $runner->pendingCount() : -1;
$allowed = initialize_is_allowed($dbCfg);
$result = null;

if (!$allowed) {
    http_response_code(403);
}

if ($allowed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if (in_array($action, ['composer', 'create_db', 'migrate', 'seed', 'full'], true)) {
        $result = run_initialize_action($action, $dbCfg);
        $vendorReady = $composer->isInstalled();
        if ($connError === null) {
            $pending = $runner->pendingCount();
        }
    }
}

$title = 'Project Setup';
$heading = 'ShopHub Setup';
$subheading = 'Install dependencies, run migrations, and seed demo data';

ob_start();
?>
<style>
  .setup-meta { display: grid; gap: 10px; margin-bottom: 20px; font-size: 13px; }
  .setup-meta div { display: flex; justify-content: space-between; gap: 12px; padding: 10px 12px; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 4px; }
  .setup-meta span:last-child { font-weight: 600; text-align: right; }
  .status-ok { color: #166534; }
  .status-error { color: #991b1b; }
  .status-warn { color: #92400e; }
  .setup-actions { display: grid; gap: 10px; margin-bottom: 20px; }
  .setup-actions form { margin: 0; }
  .btn-outline { background: #fff; color: var(--primary-color); border: 1px solid var(--primary-color); }
  .btn-outline:hover { background: var(--gray-50); }
  .setup-steps { margin-top: 16px; border: 1px solid var(--gray-200); border-radius: 4px; overflow: hidden; }
  .setup-step { display: grid; grid-template-columns: 1fr auto; gap: 8px; padding: 10px 12px; border-bottom: 1px solid var(--gray-100); font-size: 13px; }
  .setup-step:last-child { border-bottom: 0; }
  .setup-step .tag { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
  .setup-note { font-size: 12px; color: var(--gray-500); line-height: 1.5; margin-bottom: 16px; }
  .cred-box { margin-top: 12px; padding: 12px; background: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 13px; }
  .cred-box p { margin: 0 0 6px; }
  .setup-output { margin-top: 4px; padding: 8px; background: var(--gray-50); border-radius: 4px; font-family: monospace; font-size: 11px; white-space: pre-wrap; word-break: break-word; max-height: 160px; overflow: auto; color: var(--gray-600); }
  .blocked { text-align: center; color: var(--gray-600); font-size: 14px; }
</style>

<?php if (!$allowed): ?>
  <p class="blocked">Setup is disabled. Enable <code>APP_DEBUG=1</code> in <code>backend/.env</code>, or use the CLI scripts.</p>
  <a href="/admin/pages/auth/login.php" class="btn btn-primary w-100" style="margin-top:16px;display:block;text-align:center;text-decoration:none;">Go to admin login</a>
<?php else: ?>
  <p class="setup-note">
    Make sure MySQL is running in XAMPP and <code>backend/.env</code> has the correct
    <code>DB_*</code> values (usually <code>root</code> with an empty password).
  </p>

  <div class="setup-meta">
    <div>
      <span>Composer / vendor</span>
      <span class="<?= $vendorReady ? 'status-ok' : 'status-warn' ?>">
        <?= $vendorReady ? 'Installed' : 'Missing — run composer install' ?>
      </span>
    </div>
    <div><span>Database</span><span><?= e($dbCfg['database']) ?></span></div>
    <div><span>Host</span><span><?= e($dbCfg['host'] . ':' . $dbCfg['port']) ?></span></div>
    <div>
      <span>Connection</span>
      <span class="<?= $connError === null ? 'status-ok' : 'status-error' ?>">
        <?= $connError === null ? 'Connected' : 'Failed' ?>
      </span>
    </div>
    <?php if ($connError !== null): ?>
      <div><span>Error</span><span class="status-error" style="font-size:12px;"><?= e($connError) ?></span></div>
    <?php else: ?>
      <div>
        <span>Pending migrations</span>
        <span class="<?= $pending > 0 ? 'status-warn' : 'status-ok' ?>"><?= e((string) $pending) ?></span>
      </div>
    <?php endif; ?>
  </div>

  <div class="setup-actions">
    <form method="post">
      <input type="hidden" name="action" value="composer">
      <button type="submit" class="btn btn-outline w-100">1. Composer install (PHP dependencies)</button>
    </form>

    <?php if ($connError !== null): ?>
      <form method="post">
        <input type="hidden" name="action" value="create_db">
        <button type="submit" class="btn btn-outline w-100">2. Create database</button>
      </form>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="migrate">
      <button type="submit" class="btn btn-primary w-100">3. Run migrations (SQL tables)</button>
    </form>

    <form method="post">
      <input type="hidden" name="action" value="seed">
      <button type="submit" class="btn btn-outline w-100">4. Seed demo data</button>
    </form>

    <form method="post">
      <input type="hidden" name="action" value="full">
      <button type="submit" class="btn btn-primary w-100">Run all (composer + migrate + seed)</button>
    </form>
  </div>

  <?php if ($result !== null): ?>
    <div class="setup-steps">
      <?php foreach ($result['steps'] as $step): ?>
        <?php
        $tagClass = match ($step['status']) {
            'ok', 'skip' => 'status-ok',
            'error' => 'status-error',
            default => 'status-warn',
        };
        ?>
        <div class="setup-step">
          <div>
            <strong><?= e($step['label']) ?></strong>
            <div style="color:var(--gray-500);margin-top:2px;"><?= e($step['message']) ?></div>
            <?php if (!empty($step['output'])): ?>
              <pre class="setup-output"><?= e($step['output']) ?></pre>
            <?php endif; ?>
          </div>
          <span class="tag <?= e($tagClass) ?>"><?= e($step['status']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($result['credentials'])): ?>
      <div class="cred-box">
        <p><strong>Demo accounts created:</strong></p>
        <?php foreach ($result['credentials'] as $role => $cred): ?>
          <p>
            <?= e(ucfirst($role)) ?>:
            <code><?= e($cred['email']) ?></code> /
            <code><?= e($cred['password']) ?></code>
          </p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <a href="/admin/pages/auth/login.php" class="link" style="display:block;text-align:center;margin-top:16px;">Go to admin login →</a>
<?php endif; ?>
<?php
$bodyHtml = (string) ob_get_clean();
require __DIR__ . '/../includes/auth-shell.php';
