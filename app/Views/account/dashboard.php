<section class="page-hero"><div class="container"><h1>A minha conta</h1><p>Olá, <?= e(auth_user()['name'] ?? '') ?>.</p></div></section>
<section>
  <div class="container account-grid">
    <a class="form-card account-card" href="<?= base_url('conta/pedidos') ?>">
      <h2 style="font-family:var(--font-display);margin:0"><?= icon('ticket') ?><span>Pedidos</span></h2>
      <p style="color:var(--sand-dim)"><?= count($orders) ?> pedido(s)</p>
    </a>
    <a class="form-card account-card" href="<?= base_url('conta/bilhetes') ?>">
      <h2 style="font-family:var(--font-display);margin:0"><?= icon('eye') ?><span>Bilhetes</span></h2>
      <p style="color:var(--sand-dim)"><?= count($tickets) ?> bilhete(s) ativos</p>
    </a>
  </div>
</section>
