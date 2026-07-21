<div class="toolbar-row">
  <div>
    <h1 style="font-family:var(--font-display);margin:0">Painel do Produtor</h1>
    <p style="color:var(--sand-dim);margin:.35rem 0 0">Gerir eventos, vendas e validação de bilhetes.</p>
  </div>
  <a class="btn btn-primary" href="<?= base_url('produtor/eventos/novo') ?>">+ Inserir evento</a>
</div>

<div class="stats" style="margin-top:1rem">
  <div class="stat">Eventos<strong><?= count($events) ?></strong></div>
  <div class="stat">Pedidos<strong><?= count($orders) ?></strong></div>
  <div class="stat">Publicados<strong><?= count(array_filter($events, fn($e) => $e['status'] === 'published')) ?></strong></div>
  <div class="stat"><a class="btn btn-ghost btn-sm" href="<?= base_url('produtor/eventos') ?>">Ver todos</a></div>
</div>

<div class="toolbar-row" style="margin-top:1.25rem">
  <h2 style="font-family:var(--font-display);margin:0;font-size:1.25rem">Eventos recentes</h2>
  <a class="btn btn-primary btn-sm" href="<?= base_url('produtor/eventos/novo') ?>">Inserir evento</a>
</div>

<?php if (!$events): ?>
  <div class="form-card empty-state" style="margin-top:1rem;text-align:center;padding:2rem 1rem">
    <p style="color:var(--sand-dim);margin:0 0 1rem">Ainda sem eventos.</p>
    <a class="btn btn-primary" href="<?= base_url('produtor/eventos/novo') ?>">Inserir evento</a>
  </div>
<?php else: ?>
  <div class="manage-events-grid" style="margin-top:1rem">
    <?php foreach (array_slice($events, 0, 6) as $ev): ?>
      <article class="manage-event-card">
        <div class="manage-event-photo">
          <img src="<?= e(event_image($ev['image'] ?? null)) ?>" alt="<?= e($ev['title']) ?>" width="400" height="250" loading="lazy">
          <span class="badge manage-event-status"><?= e(label_event_status($ev['status'])) ?></span>
        </div>
        <div class="manage-event-body">
          <h3><?= e($ev['title']) ?></h3>
          <p class="meta"><?= e(format_datetime($ev['starts_at'])) ?></p>
          <div class="manage-event-actions">
            <a class="btn btn-primary btn-sm" href="<?= base_url('produtor/eventos/' . $ev['id'] . '/editar') ?>">Editar</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
