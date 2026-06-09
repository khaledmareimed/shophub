<?php
$messages = flash_pull();
if ($messages !== []):
?>
<div class="flash-stack" role="status" aria-live="polite" style="position:fixed;top:80px;right:20px;z-index:1000;display:flex;flex-direction:column;gap:8px;max-width:360px;">
  <?php foreach ($messages as $m):
    $type = $m['type'] ?? 'info';
    $bg = match ($type) {
      'success' => '#dcfce7', 'error' => '#fee2e2', 'warning' => '#fef3c7', default => '#dbeafe'
    };
    $color = match ($type) {
      'success' => '#166534', 'error' => '#991b1b', 'warning' => '#854d0e', default => '#1e3a8a'
    };
  ?>
    <div class="flash-item flash-<?= e($type) ?>" style="background:<?= $bg ?>;color:<?= $color ?>;padding:12px 16px;border-radius:8px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
      <?= e($m['message']) ?>
    </div>
  <?php endforeach; ?>
</div>
<script>setTimeout(()=>{document.querySelectorAll('.flash-item').forEach(el=>el.style.display='none');},5000);</script>
<?php endif; ?>
