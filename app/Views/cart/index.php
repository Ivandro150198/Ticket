<?php
$currency = $_SESSION['cart_currency'] ?? (isset($items[array_key_first($items ?? [])]) ? ($items[array_key_first($items)]['currency'] ?? 'EUR') : 'EUR');
$country = $_SESSION['cart_country'] ?? 'PT';
?>
<section class="page-hero"><div class="container"><h1>Carrinho</h1><?php if ($items): ?><p><?= e(label_country($country)) ?> · <?= e($currency === 'XOF' ? 'FCFA' : 'Euro') ?></p><?php endif; ?></div></section>
<section>
  <div class="container">
    <div class="form-card">
      <?php if (!$items): ?>
        <p>O carrinho está vazio. <a href="<?= base_url('eventos') ?>">Explorar eventos</a></p>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Evento</th><th>Tipo</th><th>Lugares</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($items as $key => $item): ?>
                <tr>
                  <td><a href="<?= base_url('evento/' . $item['slug']) ?>"><?= e($item['event_title']) ?></a></td>
                  <td><?= e($item['ticket_name']) ?></td>
                  <td><?= !empty($item['seat_ids']) ? count($item['seat_ids']) . ' lugar(es)' : '—' ?></td>
                  <td><?= money((float)$item['unit_price'], $currency) ?></td>
                  <td>
                    <?php if (empty($item['seat_ids'])): ?>
                      <form method="post" action="<?= base_url('carrinho/atualizar') ?>" style="display:flex;gap:.35rem">
                        <?= csrf_field() ?>
                        <input type="hidden" name="cart_key" value="<?= e((string)$key) ?>">
                        <input class="qty-input" type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="0" max="10">
                        <button class="btn btn-ghost btn-sm" type="submit">OK</button>
                      </form>
                    <?php else: ?>
                      <?= (int)$item['qty'] ?>
                    <?php endif; ?>
                  </td>
                  <td><?= money((float)$item['unit_price'] * (int)$item['qty'], $currency) ?></td>
                  <td>
                    <form method="post" action="<?= base_url('carrinho/remover') ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="cart_key" value="<?= e((string)$key) ?>">
                      <button class="btn btn-danger btn-sm" type="submit">Remover</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if ($currency === 'EUR'): ?>
        <form method="post" action="<?= base_url('carrinho/cupao') ?>" class="stack-form" style="margin-top:1rem">
          <?= csrf_field() ?>
          <input name="coupon" placeholder="Cupão (ex: BEMVINDO10)" value="<?= e($coupon['code'] ?? '') ?>">
          <button class="btn btn-ghost" type="submit">Aplicar cupão</button>
        </form>
        <?php if ($coupon): ?>
          <form method="post" action="<?= base_url('carrinho/cupao/remover') ?>" style="margin-top:.5rem">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-danger" type="submit">Remover cupão <?= e($coupon['code']) ?></button>
          </form>
        <?php endif; ?>
        <?php endif; ?>

        <p style="margin-top:1rem">Subtotal: <?= money($total, $currency) ?></p>
        <?php if ($discount > 0): ?><p>Desconto: -<?= money($discount, $currency) ?></p><?php endif; ?>
        <p style="font-size:1.2rem"><strong>Total: <?= money($payable, $currency) ?></strong></p>
        <a class="btn btn-primary" href="<?= base_url('checkout') ?>">Continuar para pagamento</a>
      <?php endif; ?>
    </div>
  </div>
</section>
