<section class="page-hero"><div class="container"><h1>MB Way</h1><p>Pedido #<?= (int)$order['id'] ?> · <?= money($payable, $order['currency'] ?? 'EUR') ?></p></div></section>
<section>
  <div class="container form-card" style="max-width:520px">
    <p>Foi enviado um pedido MB Way para o telemóvel associado (modo de demonstração).</p>
    <p>Referência: <code><?= e($order['payment_ref'] ?? '') ?></code></p>
    <form method="post" action="<?= base_url('pagamento/confirmar') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
      <button class="btn btn-primary" type="submit">Simular confirmação MB Way</button>
    </form>
  </div>
</section>
