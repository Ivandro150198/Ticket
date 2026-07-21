<section class="page-hero"><div class="container"><h1>Multibanco</h1><p>Pedido #<?= (int)$order['id'] ?></p></div></section>
<section>
  <div class="container form-card" style="max-width:520px">
    <p>Entidade: <strong><?= e($ref['entity']) ?></strong></p>
    <p>Referência: <strong><?= e($ref['reference']) ?></strong></p>
    <p>Montante: <strong><?= money($payable, $order['currency'] ?? 'EUR') ?></strong></p>
    <p style="color:var(--sand-dim)">Em produção a confirmação chega automaticamente pelo sistema de pagamento. Nesta demonstração, confirme manualmente:</p>
    <form method="post" action="<?= base_url('pagamento/confirmar') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
      <button class="btn btn-primary" type="submit">Já paguei — confirmar</button>
    </form>
  </div>
</section>
