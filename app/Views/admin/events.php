<div class="toolbar-row">
  <div>
    <h1 style="font-family:var(--font-display);margin:0">Gestão de eventos</h1>
    <p style="color:var(--sand-dim);margin:.35rem 0 0"><?= count($events) ?> evento(s) na plataforma</p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('produtor/eventos/novo') ?>">+ Inserir evento</a>
</div>

<?php if (!$events): ?>
  <div class="form-card empty-state" style="margin-top:1rem;text-align:center;padding:2rem 1rem">
    <p style="color:var(--sand-dim);margin:0 0 1rem">Sem eventos. Insira o primeiro evento.</p>
    <a class="btn btn-primary" href="<?= base_url('produtor/eventos/novo') ?>">Inserir evento</a>
  </div>
<?php else: ?>
  <div class="manage-events-grid" style="margin-top:1rem">
    <?php foreach ($events as $ev): ?>
      <article class="manage-event-card">
        <div class="manage-event-photo">
          <img src="<?= e(event_image($ev['image'] ?? null)) ?>" alt="<?= e($ev['title']) ?>" width="400" height="250" loading="lazy">
          <span class="badge manage-event-status"><?= e(label_event_status($ev['status'])) ?></span>
        </div>
        <div class="manage-event-body">
          <h3><?= e($ev['title']) ?></h3>
          <p class="meta">
            <?= e($ev['venue'] ?? '') ?>, <?= e($ev['city'] ?? '') ?><br>
            <?= e(format_datetime($ev['starts_at'])) ?>
          </p>
          <p class="meta">Produtor: <?= e($ev['producer_name'] ?? '—') ?></p>
          <form method="post" action="<?= base_url('admin/eventos/estado') ?>" class="manage-event-status-form">
            <?= csrf_field() ?>
            <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
            <select name="status">
              <?php foreach (['draft','pending','published','cancelled'] as $st): ?>
                <option value="<?= $st ?>" <?= $ev['status'] === $st ? 'selected' : '' ?>><?= e(label_event_status($st)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" type="submit">Atualizar</button>
          </form>
          <div class="manage-event-actions">
            <a class="btn btn-primary btn-sm" href="<?= base_url('produtor/eventos/' . $ev['id'] . '/editar') ?>">Editar</a>
            <?php if ($ev['status'] === 'published'): ?>
              <a class="btn btn-ghost btn-sm" href="<?= base_url('evento/' . $ev['slug']) ?>" target="_blank" rel="noopener">Ver</a>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
