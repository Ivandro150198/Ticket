<h1 style="font-family:var(--font-display);margin-top:0">Mensagens de contacto</h1>
<div class="form-card">
  <?php foreach ($messages as $m): ?>
    <div style="border-bottom:1px solid var(--line);padding:1rem 0">
      <strong><?= e($m['subject']) ?></strong>
      <div class="meta"><?= e($m['name']) ?> · <?= e($m['email']) ?> · <?= e($m['created_at']) ?></div>
      <p style="color:var(--sand-dim)"><?= nl2br(e($m['message'])) ?></p>
    </div>
  <?php endforeach; ?>
  <?php if (!$messages): ?><p>Sem mensagens.</p><?php endif; ?>
</div>
