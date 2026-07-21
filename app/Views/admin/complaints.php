<h1 style="font-family:var(--font-display);margin-top:0">Reclamações</h1>
<div class="form-card">
  <?php foreach ($complaints as $c): ?>
    <div style="border-bottom:1px solid var(--line);padding:1rem 0">
      <strong>#<?= (int)$c['id'] ?> · <?= e($c['subject']) ?></strong>
      <div class="meta"><?= e($c['name']) ?> · <?= e($c['email']) ?> · <?= e($c['created_at']) ?> · <?= e($c['status']) ?></div>
      <p style="color:var(--sand-dim)"><?= nl2br(e($c['message'])) ?></p>
      <form method="post" action="<?= base_url('admin/reclamacoes/estado') ?>" style="display:flex;gap:.4rem">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <select name="status">
          <?php foreach (['open','in_progress','closed'] as $st): ?>
            <option value="<?= $st ?>" <?= $c['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Atualizar</button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$complaints): ?><p>Sem reclamações.</p><?php endif; ?>
</div>
