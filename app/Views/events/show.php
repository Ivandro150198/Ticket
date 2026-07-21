<section class="page-hero">
  <div class="container">
    <h1><?= e($event['title']) ?></h1>
    <p><?= e(format_datetime($event['starts_at'])) ?> · <?= e($event['venue']) ?>, <?= e($event['city']) ?> · <?= e(label_country($event['country'] ?? 'PT')) ?></p>
    <p>
      <span class="badge badge-ok"><?= e(label_age_rating($event['age_rating'] ?? 'Todos')) ?></span>
      <?php if (!empty($event['promoter_name'])): ?>
        · Promotor: <?= e($event['promoter_name']) ?><?= !empty($event['promoter_nif']) ? ' (NIF ' . e($event['promoter_nif']) . ')' : '' ?>
      <?php endif; ?>
      <?php if (!empty($event['capacity'])): ?>
        · Lotação: <?= (int) $event['capacity'] ?>
      <?php endif; ?>
    </p>
  </div>
</section>
<section style="padding-top:1rem">
  <div class="container event-detail">
    <div>
      <div class="event-cover">
        <img src="<?= e(event_image($event['image'])) ?>" alt="<?= e($event['title']) ?>" width="960" height="600" loading="eager">
      </div>
      <div class="detail-card" style="margin-top:1rem">
        <h2 style="font-family:var(--font-display);margin-top:0">Sobre o evento</h2>
        <p style="color:var(--sand-dim);white-space:pre-line"><?= e($event['description']) ?></p>
      </div>
      <?php if (!empty($event['has_seats']) && $seats): ?>
        <div class="detail-card" style="margin-top:1rem">
          <h2 style="font-family:var(--font-display);margin-top:0">Mapa de lugares</h2>
          <p style="color:var(--sand-dim)">Selecione os lugares disponíveis (verde).</p>
          <div class="seat-map" id="seat-map">
            <?php
              $byRow = [];
              foreach ($seats as $s) {
                  $byRow[$s['row_label']][] = $s;
              }
              foreach ($byRow as $row => $list):
            ?>
              <div class="seat-row">
                <span class="seat-row-label"><?= e($row) ?></span>
                <?php foreach ($list as $s): ?>
                  <button type="button"
                    class="seat <?= e($s['status']) ?>"
                    data-seat-id="<?= (int)$s['id'] ?>"
                    data-status="<?= e($s['status']) ?>"
                    <?= $s['status'] !== 'available' ? 'disabled' : '' ?>
                    title="<?= e($row . $s['seat_number']) ?>">
                    <?= (int)$s['seat_number'] ?>
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <div class="form-card">
      <h2 style="font-family:var(--font-display);margin-top:0">Bilhetes</h2>
      <?php foreach ($types as $type): ?>
        <?php
          $eff = TicketType::effectivePrice($type);
          $hasPromo = !empty($type['promo_price']) && (float)$type['promo_price'] > 0 && (float)$type['promo_price'] < (float)$type['price'];
        ?>
        <div class="ticket-type">
          <div>
            <strong><?= e($type['name']) ?></strong><br>
            <span class="price">
              <?php if ($hasPromo): ?><span class="old"><?= money((float)$type['price'], $event['currency'] ?? 'EUR') ?></span><?php endif; ?>
              <?= money($eff, $event['currency'] ?? 'EUR') ?><?= ($event['currency'] ?? 'EUR') === 'EUR' ? ' <small>+IVA</small>' : '' ?>
            </span>
            <div class="meta">Disponíveis: <?= (int)$type['stock'] ?></div>
          </div>
          <?php if ((int)$type['stock'] > 0): ?>
            <form action="<?= base_url('carrinho/adicionar') ?>" method="post" class="seat-form">
              <?= csrf_field() ?>
              <input type="hidden" name="ticket_type_id" value="<?= (int)$type['id'] ?>">
              <div class="selected-seats"></div>
              <input class="qty-input" type="number" name="qty" value="1" min="1" max="<?= min(10, (int)$type['stock']) ?>" <?= !empty($event['has_seats']) ? 'readonly' : '' ?>>
              <button class="btn btn-primary btn-sm" type="submit">Adicionar</button>
            </form>
          <?php else: ?>
            <span class="badge badge-bad">Esgotado</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php if (!empty($event['has_seats'])): ?>
<script>
(function(){
  const selected = new Set();
  const map = document.getElementById('seat-map');
  if (!map) return;
  map.addEventListener('click', (e) => {
    const btn = e.target.closest('.seat');
    if (!btn || btn.disabled) return;
    const id = btn.dataset.seatId;
    if (selected.has(id)) { selected.delete(id); btn.classList.remove('selected'); }
    else { selected.add(id); btn.classList.add('selected'); }
    document.querySelectorAll('.seat-form').forEach(form => {
      form.querySelectorAll('input[name="seat_ids[]"]').forEach(i => i.remove());
      selected.forEach(sid => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'seat_ids[]'; inp.value = sid;
        form.querySelector('.selected-seats').appendChild(inp);
      });
      const qty = form.querySelector('input[name="qty"]');
      if (qty) qty.value = Math.max(1, selected.size || 1);
    });
  });
})();
</script>
<?php endif; ?>
