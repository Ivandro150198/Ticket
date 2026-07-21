<section class="page-hero">
  <div class="container">
    <h1>Eventos disponíveis</h1>
    <p><?= (int) $total ?> eventos publicados. Escolha o seu e garanta o bilhete.</p>
  </div>
</section>
<section style="padding-top:1rem">
  <div class="container">
    <div class="event-grid">
      <?php foreach ($events as $ev): ?>
        <article class="event-card">
          <a href="<?= base_url('evento/' . $ev['slug']) ?>">
            <div class="event-card-media">
              <img src="<?= e(event_image($ev['image'])) ?>" alt="<?= e($ev['title']) ?>" width="640" height="400" loading="lazy">
            </div>
            <div class="event-card-body">
              <h3><?= e($ev['title']) ?></h3>
              <div class="meta"><?= e($ev['venue']) ?>, <?= e($ev['city']) ?> · <?= e(label_country($ev['country'] ?? 'PT')) ?><br><?= e(format_datetime($ev['starts_at'])) ?></div>
              <div class="event-card-footer">
                <div class="price"><?= event_price_label($ev['min_price'] !== null ? (float)$ev['min_price'] : null, $ev['max_price'] !== null ? (float)$ev['max_price'] : null, $ev['currency'] ?? 'EUR') ?></div>
                <div class="stock-left<?= (int)($ev['stock_remaining'] ?? 0) <= 0 ? ' stock-left--out' : '' ?>"><?= e(event_stock_label(isset($ev['stock_remaining']) ? (int)$ev['stock_remaining'] : null)) ?></div>
              </div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
      <div class="pagination">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= base_url('eventos?page=' . $p) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
