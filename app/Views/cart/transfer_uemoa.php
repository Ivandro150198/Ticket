<section class="page-hero"><div class="container"><h1>Transferência bancária</h1><p>Pagamento por transferência na zona UEMOA (Guiné-Bissau).</p></div></section>
<section>
  <div class="container">
    <div class="form-card" style="max-width:560px;margin:0 auto">
      <p style="color:var(--sand-dim)">Em produção a confirmação chega automaticamente. Nesta demonstração, confirme após transferir:</p>
      <p><strong>Banco:</strong> <?= e($ref['bank']) ?></p>
      <p><strong>IBAN:</strong> <code><?= e($ref['iban']) ?></code></p>
      <p><strong>Referência:</strong> <code><?= e($ref['reference']) ?></code></p>
      <p><strong>Valor:</strong> <?= money($payable, $order['currency'] ?? 'XOF') ?></p>
      <form method="post" action="<?= base_url('pagamento/confirmar') ?>" style="margin-top:1rem">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <button class="btn btn-primary" type="submit">Já transferi — confirmar</button>
      </form>
    </div>
  </div>
</section>
