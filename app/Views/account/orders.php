<section class="page-hero"><div class="container"><h1>Os meus pedidos</h1></div></section>
<section>
  <div class="container form-card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Total</th><th>Estado</th><th>Data</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><?= (int)$o['id'] ?></td>
              <td><?= money(max(0, (float)$o['total'] - (float)($o['discount'] ?? 0))) ?></td>
              <td><?= e(label_order_status($o['status'])) ?><?= $o['refunded_at'] ? ' (reembolsado)' : '' ?></td>
              <td><?= e($o['created_at']) ?></td>
              <td style="display:flex;gap:.4rem;flex-wrap:wrap">
                <a class="btn btn-ghost btn-sm" href="<?= base_url('pedido/' . $o['id']) ?>">Ver</a>
                <?php if ($o['status'] === 'paid' && empty($o['refunded_at'])): ?>
                  <form method="post" action="<?= base_url('conta/reembolso') ?>" onsubmit="return confirm('Pedir reembolso deste pedido?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                    <input type="hidden" name="reason" value="Pedido pelo cliente">
                    <button class="btn btn-danger btn-sm" type="submit">Reembolsar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?><tr><td colspan="5">Sem pedidos.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
