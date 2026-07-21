<section class="page-hero"><div class="container"><h1>Orange Money</h1><p>Pagamento móvel na Guiné-Bissau (modo demonstração).</p></div></section>
<section>
  <div class="container">
    <div class="form-card" style="max-width:520px;margin:0 auto">
      <p>Foi enviado um pedido Orange Money para o número associado (demonstração).</p>
      <p><strong>Pedido #<?= (int)$order['id'] ?></strong></p>
      <p>Valor: <strong><?= money($payable, $order['currency'] ?? 'XOF') ?></strong></p>
      <?php if (!empty($order['payment_ref'])): ?>
        <p>Referência: <code><?= e($order['payment_ref']) ?></code></p>
      <?php endif; ?>
      <form method="post" action="<?= base_url('pagamento/confirmar') ?>" style="margin-top:1rem">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <button class="btn btn-primary" type="submit">Confirmar pagamento Orange Money</button>
      </form>
    </div>
  </div>
</section>
