<div class="toolbar-row">
  <div>
    <h1 style="font-family:var(--font-display);margin:0">Gestão de eventos</h1>
    <p style="color:var(--sand-dim);margin:.35rem 0 0"><?= count($events) ?> evento(s)</p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('produtor/eventos/novo') ?>">+ Inserir evento</a>
</div>

<?php if (!$events): ?>
  <div class="form-card empty-state" style="margin-top:1rem;text-align:center;padding:2rem 1rem">
    <p style="color:var(--sand-dim);margin:0 0 1rem">Ainda não há eventos. Crie o primeiro para começar a vender bilhetes.</p>
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
            <?= e($ev['venue'] ?? '') ?><?= !empty($ev['city']) ? ', ' . e($ev['city']) : '' ?><br>
            <?= e(format_datetime($ev['starts_at'])) ?>
          </p>
          <?php if (!empty($ev['producer_name'])): ?>
            <p class="meta">Produtor: <?= e($ev['producer_name']) ?></p>
          <?php endif; ?>
          <div class="manage-event-actions">
            <a class="btn btn-primary btn-sm" href="<?= base_url('produtor/eventos/' . $ev['id'] . '/editar') ?>">Editar</a>
            <?php if (($ev['status'] ?? '') === 'published'): ?>
              <a class="btn btn-ghost btn-sm" href="<?= base_url('evento/' . $ev['slug']) ?>" target="_blank" rel="noopener">Ver</a>
            <?php endif; ?>
            <form method="post" action="<?= base_url('produtor/eventos/' . $ev['id'] . '/eliminar') ?>" onsubmit="return confirm('Eliminar este evento?')">
              <?= csrf_field() ?>
              <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
