<div class="toolbar-row">
  <h1 style="font-family:var(--font-display);margin:0"><?= $event ? 'Editar evento' : 'Inserir evento' ?></h1>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('produtor/eventos') ?>">← Voltar à lista</a>
</div>

<form class="form-card form-grid" style="margin-top:1rem" method="post" enctype="multipart/form-data"
  action="<?= $event ? base_url('produtor/eventos/' . $event['id']) : base_url('produtor/eventos') ?>">
  <?= csrf_field() ?>
  <div class="form-row">
    <label>Título<input name="title" required value="<?= e($event['title'] ?? '') ?>" placeholder="Nome do evento"></label>
    <label>Slug<input name="slug" value="<?= e($event['slug'] ?? '') ?>" placeholder="gerado automaticamente"></label>
  </div>
  <label>Descrição<textarea name="description" required placeholder="Descreva o evento..."><?= e($event['description'] ?? '') ?></textarea></label>
  <div class="form-row">
    <label>Local<input name="venue" required value="<?= e($event['venue'] ?? '') ?>" placeholder="Sala / recinto"></label>
    <label>Cidade<input name="city" required value="<?= e($event['city'] ?? '') ?>" placeholder="Lisboa ou Bissau"></label>
  </div>
  <div class="form-row">
    <label>País
      <select name="country" id="event-country">
        <?php foreach (countries_config() as $code => $meta): ?>
          <option value="<?= e($code) ?>" <?= ($event['country'] ?? 'PT') === $code ? 'selected' : '' ?>>
            <?= e($meta['name']) ?> — <?= e($meta['currency_label']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Data e hora
      <input type="datetime-local" name="starts_at" required
        value="<?= e(isset($event['starts_at']) ? date('Y-m-d\TH:i', strtotime($event['starts_at'])) : '') ?>">
    </label>
  </div>
  <div class="form-row">
    <label>Promotor / organizador
      <input name="promoter_name" required value="<?= e($event['promoter_name'] ?? (auth_user()['name'] ?? '')) ?>" placeholder="Nome legal do promotor">
    </label>
    <label>NIF do promotor
      <input name="promoter_nif" required value="<?= e($event['promoter_nif'] ?? '') ?>" placeholder="Ex.: 500000000" pattern="[0-9A-Za-z]{5,20}">
    </label>
  </div>
  <div class="form-row">
    <label>Classificação etária
      <select name="age_rating" required>
        <?php foreach (age_ratings() as $code => $label): ?>
          <option value="<?= e($code) ?>" <?= ($event['age_rating'] ?? 'Todos') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Lotação do recinto (opcional)
      <input type="number" name="capacity" min="1" value="<?= e(isset($event['capacity']) && $event['capacity'] !== null ? (string) $event['capacity'] : '') ?>" placeholder="Ex.: 500">
    </label>
  </div>
  <label>Foto do evento
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </label>

  <?php if (!empty($event['image']) || !$event): ?>
    <div class="form-image-preview">
      <span class="meta">Pré-visualização da foto</span>
      <img src="<?= e(event_image($event['image'] ?? null)) ?>" alt="Pré-visualização" width="320" height="200" id="image-preview">
    </div>
  <?php endif; ?>

  <?php if (auth_role() === 'admin'): ?>
    <div class="form-row">
      <label>Estado
        <select name="status">
          <?php foreach (['draft','pending','published','cancelled'] as $st): ?>
            <option value="<?= $st ?>" <?= ($event['status'] ?? 'published') === $st ? 'selected' : '' ?>><?= e(label_event_status($st)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="check-inline">
        <input type="checkbox" name="featured" value="1" <?= !empty($event['featured']) ? 'checked' : '' ?>>
        Destaque na página inicial
      </label>
    </div>
  <?php endif; ?>

  <h2 style="font-family:var(--font-display);margin:1rem 0 0">Tipos de bilhete</h2>
  <p class="meta" style="margin:0">Defina pelo menos um tipo (ex.: Geral, VIP).</p>
  <div data-types class="form-grid">
    <?php if ($types): ?>
      <?php foreach ($types as $t): ?>
        <div class="form-row type-row">
          <input type="hidden" name="type_id[]" value="<?= (int)$t['id'] ?>">
          <label>Nome<input name="type_name[]" required value="<?= e($t['name']) ?>"></label>
          <label>Preço<input name="type_price[]" type="number" step="0.01" min="0" required value="<?= e($t['price']) ?>"></label>
          <label>Promoção<input name="type_promo[]" type="number" step="0.01" min="0" value="<?= e($t['promo_price'] ?? '') ?>"></label>
          <label>Disponíveis<input name="type_stock[]" type="number" min="0" required value="<?= (int)$t['stock'] ?>"></label>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="form-row type-row">
        <input type="hidden" name="type_id[]" value="0">
        <label>Nome<input name="type_name[]" required placeholder="Geral" value="Geral"></label>
        <label>Preço<input name="type_price[]" type="number" step="0.01" min="0" required value="15.00"></label>
        <label>Promoção<input name="type_promo[]" type="number" step="0.01" min="0"></label>
        <label>Disponíveis<input name="type_stock[]" type="number" min="0" value="100" required></label>
      </div>
    <?php endif; ?>
  </div>
  <button class="btn btn-ghost btn-sm" type="button" data-add-type-row>+ Adicionar tipo</button>
  <div class="toolbar-row" style="margin-top:.5rem">
    <button class="btn btn-primary" type="submit"><?= $event ? 'Guardar alterações' : 'Criar evento' ?></button>
  </div>
</form>

<?php if ($event): ?>
<div class="form-card" style="margin-top:1rem">
  <h2 style="font-family:var(--font-display);margin-top:0">Mapa de lugares</h2>
  <form class="form-row" method="post" action="<?= base_url('produtor/eventos/' . $event['id'] . '/lugares') ?>">
    <?= csrf_field() ?>
    <label>Filas (A,B,C)<input name="rows" value="A,B,C,D"></label>
    <label>Lugares por fila<input type="number" name="seats_per_row" value="12" min="1" max="40"></label>
    <label>Tipo bilhete (id opcional)<input type="number" name="ticket_type_id" placeholder="opcional"></label>
    <label style="display:flex;align-items:end"><button class="btn btn-primary" type="submit">Gerar lugares</button></label>
  </form>
</div>
<?php endif; ?>

<script>
document.querySelector('input[name="image"]')?.addEventListener('change', function (e) {
  const file = e.target.files?.[0];
  const img = document.getElementById('image-preview');
  if (!file || !img) return;
  img.src = URL.createObjectURL(file);
});
</script>
