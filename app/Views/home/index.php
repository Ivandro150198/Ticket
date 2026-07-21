<?php
$heroSlides = $heroSlides ?? [];
$heroFirst = $heroSlides[0] ?? null;
?>
<section class="hero hero-3d" data-hero-3d aria-roledescription="carrossel" aria-label="Eventos em destaque">
  <div class="hero-stage" data-hero-stage>
    <?php if ($heroSlides): ?>
      <?php foreach ($heroSlides as $i => $slide): ?>
        <div class="hero-panel<?= $i === 0 ? ' is-active' : '' ?>"
             data-hero-panel
             data-title="<?= e($slide['title']) ?>"
             data-meta="<?= e(format_datetime($slide['starts_at'])) ?> · <?= e($slide['city']) ?> · <?= e(label_country($slide['country'] ?? 'PT')) ?>"
             data-href="<?= e(base_url('evento/' . $slide['slug'])) ?>"
             aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
          <img class="hero-panel-img" src="<?= e(event_image($slide['image'] ?? null)) ?>" alt="<?= e($slide['title']) ?>" width="1600" height="900" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="hero-panel is-active" data-hero-panel data-title="Próximos eventos" data-meta="Portugal e Guiné-Bissau" data-href="<?= e(base_url('eventos')) ?>" aria-hidden="false">
        <div class="hero-panel-fallback"></div>
      </div>
    <?php endif; ?>
  </div>
  <div class="hero-shade"></div>
  <div class="hero-noise"></div>

  <div class="container hero-content">
    <h1 class="hero-brand">Event<span>Ticket</span>-GB</h1>
    <p class="hero-lead" data-hero-lead>
      <?php if ($heroFirst): ?>
        <?= e($heroFirst['title']) ?>
      <?php else: ?>
        O seu bilhete para eventos em Portugal e Guiné-Bissau.
      <?php endif; ?>
    </p>
    <p class="hero-meta" data-hero-meta>
      <?php if ($heroFirst): ?>
        <?= e(format_datetime($heroFirst['starts_at'])) ?> · <?= e($heroFirst['city']) ?> · <?= e(label_country($heroFirst['country'] ?? 'PT')) ?>
      <?php else: ?>
        Compra simples · PDF com QR · Validação na porta
      <?php endif; ?>
    </p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?= $heroFirst ? base_url('evento/' . $heroFirst['slug']) : base_url('eventos') ?>" data-hero-cta>
        <?= $heroFirst ? 'Ver este evento' : 'Ver eventos' ?>
      </a>
      <a class="btn btn-ghost" href="<?= base_url('eventos') ?>">Todos os eventos</a>
    </div>
  </div>

  <?php if (count($heroSlides) > 1): ?>
    <div class="hero-nav" aria-hidden="false">
      <button type="button" class="hero-nav-btn" data-hero-prev aria-label="Evento anterior">‹</button>
      <div class="hero-dots" data-hero-dots role="tablist" aria-label="Selecionar evento">
        <?php foreach ($heroSlides as $i => $slide): ?>
          <button type="button" class="hero-dot<?= $i === 0 ? ' is-active' : '' ?>" data-hero-dot="<?= $i ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="<?= e($slide['title']) ?>"></button>
        <?php endforeach; ?>
      </div>
      <button type="button" class="hero-nav-btn" data-hero-next aria-label="Próximo evento">›</button>
    </div>
  <?php endif; ?>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Em destaque</h2>
        <p>Próximas noites e concertos que não pode perder.</p>
      </div>
      <a href="<?= base_url('eventos') ?>">Ver todos →</a>
    </div>
    <div class="slider">
      <?php foreach ($featured as $i => $ev): ?>
        <a class="slide" href="<?= base_url('evento/' . $ev['slug']) ?>" style="animation-delay: <?= $i * 0.08 ?>s">
          <img class="slide-bg" src="<?= e(event_image($ev['image'])) ?>" alt="<?= e($ev['title']) ?>" width="640" height="800" loading="lazy">
          <div class="slide-overlay"></div>
          <div class="slide-body">
            <div class="slide-date"><?= e(format_datetime($ev['starts_at'])) ?></div>
            <h3><?= e($ev['title']) ?></h3>
            <div class="slide-stock"><?= e(event_stock_label(isset($ev['stock_remaining']) ? (int)$ev['stock_remaining'] : null)) ?></div>
            <span class="btn btn-sm btn-primary">Ver detalhes</span>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (!$featured): ?>
        <p>Sem eventos em destaque de momento.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Eventos disponíveis</h2>
        <p>Alguns eventos que podem interessar.</p>
      </div>
    </div>
    <div class="event-grid">
      <?php foreach ($events as $i => $ev): ?>
        <article class="event-card" style="animation-delay: <?= $i * 0.06 ?>s">
          <a href="<?= base_url('evento/' . $ev['slug']) ?>">
            <div class="event-card-media">
              <img src="<?= e(event_image($ev['image'])) ?>" alt="<?= e($ev['title']) ?>" width="640" height="400" loading="lazy">
            </div>
            <div class="event-card-body">
              <h3><?= e($ev['title']) ?></h3>
              <div class="meta"><?= e($ev['city']) ?> · <?= e(label_country($ev['country'] ?? 'PT')) ?> · <?= e(format_datetime($ev['starts_at'])) ?></div>
              <div class="event-card-footer">
                <div class="price"><?= event_price_label($ev['min_price'] !== null ? (float)$ev['min_price'] : null, $ev['max_price'] !== null ? (float)$ev['max_price'] : null, $ev['currency'] ?? 'EUR') ?></div>
                <div class="stock-left<?= (int)($ev['stock_remaining'] ?? 0) <= 0 ? ' stock-left--out' : '' ?>"><?= e(event_stock_label(isset($ev['stock_remaining']) ? (int)$ev['stock_remaining'] : null)) ?></div>
              </div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Como funciona?</h2>
        <p>Em apenas três passos, marque presença no evento dos seus sonhos.</p>
      </div>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">01</div>
        <h3>Aceder</h3>
        <p>Abra EventTicket-GB em qualquer dispositivo e explore os eventos.</p>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Escolher</h3>
        <p>Selecione o evento, o tipo de bilhete e adicione ao carrinho.</p>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Reservar</h3>
        <p>Finalize o pagamento e receba o bilhete com código QR único (PDF/HTML).</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <div>
        <h2>São os nossos parceiros</h2>
        <p>Juntos levamos a alegria dos eventos a mais pessoas.</p>
      </div>
    </div>
    <div class="partners">
      <?php foreach ($partners as $p): ?>
        <a class="partner-pill" href="<?= e($p['website'] ?: '#') ?>" target="_blank" rel="noopener"><?= e($p['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="newsletter">
      <h2 class="newsletter-title">Newsletter EventTicket-GB</h2>
      <p class="newsletter-desc">Subscreva para receber novidades sobre eventos.</p>
      <form action="<?= base_url('newsletter') ?>" method="post">
        <?= csrf_field() ?>
        <input type="email" name="email" required placeholder="O seu e-mail">
        <button class="btn btn-primary" type="submit">Subscrever</button>
        <label class="check">
          <input type="checkbox" name="accept" value="1" required>
          Aceito receber as novidades da EventTicket-GB.
        </label>
      </form>
    </div>
  </div>
</section>
