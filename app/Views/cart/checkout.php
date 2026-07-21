<?php
$currency = $currency ?? 'EUR';
$country = $country ?? 'PT';
$paymentMethods = $paymentMethods ?? payment_methods_for_country($country, !empty($stripeEnabled));
?>
<section class="page-hero"><div class="container"><h1>Pagamento</h1><p>Métodos disponíveis para <?= e(label_country($country)) ?> · <?= e($currency === 'XOF' ? 'Franco CFA (FCFA)' : 'Euro (€)') ?>.</p></div></section>
<section>
  <div class="container event-detail">
    <div class="form-card">
      <h2 style="font-family:var(--font-display);margin-top:0">Dados do comprador</h2>
      <form class="form-grid" method="post" action="<?= base_url('checkout') ?>">
        <?= csrf_field() ?>
        <label>Nome<input type="text" name="buyer_name" required value="<?= e($user['name'] ?? '') ?>"></label>
        <label>E-mail<input type="email" name="buyer_email" required value="<?= e($user['email'] ?? '') ?>"></label>
        <label>Telefone<input type="text" name="buyer_phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="<?= $country === 'GW' ? '+245…' : '+351…' ?>"></label>
        <label>NIF (opcional, para fatura)
          <input type="text" name="buyer_nif" value="<?= e(old('buyer_nif')) ?>" placeholder="Contribuinte" maxlength="20">
        </label>
        <label>Forma de pagamento (<?= e(label_country($country)) ?>)
          <select name="payment_method">
            <?php foreach ($paymentMethods as $value => $label): ?>
              <option value="<?= e($value) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="check-inline legal-check">
          <input type="checkbox" name="accept_terms" value="1" required>
          <span>Li e aceito os <a href="<?= base_url('termos') ?>" target="_blank" rel="noopener">Termos e Condições</a> e a <a href="<?= base_url('privacidade') ?>" target="_blank" rel="noopener">Política de Privacidade</a> (RGPD), e autorizo o tratamento dos dados necessários à compra e emissão dos bilhetes.</span>
        </label>
        <p class="meta">Em caso de litígio de consumo: <a href="<?= base_url('resolucao-litigios') ?>">RAL</a> · <a href="<?= base_url('livro-de-reclamacoes') ?>">Livro de Reclamações</a>.</p>
        <button class="btn btn-primary" type="submit">Pagar <?= money($payable, $currency) ?></button>
      </form>
    </div>
    <div class="form-card">
      <h2 style="font-family:var(--font-display);margin-top:0">Resumo</h2>
      <?php foreach ($items as $item): ?>
        <div class="ticket-type">
          <div>
            <strong><?= e($item['event_title']) ?></strong><br>
            <?= e($item['ticket_name']) ?> × <?= (int)$item['qty'] ?>
          </div>
          <div><?= money((float)$item['unit_price'] * (int)$item['qty'], $currency) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if ($discount > 0): ?><p>Desconto: -<?= money($discount, $currency) ?></p><?php endif; ?>
      <p><strong>Total: <?= money($payable, $currency) ?></strong></p>
    </div>
  </div>
</section>
