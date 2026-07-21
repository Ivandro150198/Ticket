<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap">
  <h1 style="font-family:var(--font-display);margin:0">Pedidos</h1>
  <a class="btn btn-ghost" href="<?= base_url('admin/pedidos/export') ?>">Exportar CSV</a>
</div>
<div class="form-card" style="margin-top:1rem">
  <table>
    <thead><tr><th>#</th><th>Comprador</th><th>Total</th><th>Pagamento</th><th>Estado</th><th>Data</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><a href="<?= base_url('pedido/' . $o['id']) ?>"><?= (int)$o['id'] ?></a></td>
          <td><?= e($o['buyer_name']) ?><br><small><?= e($o['buyer_email']) ?></small></td>
          <td><?= money(max(0, (float)$o['total'] - (float)($o['discount'] ?? 0))) ?></td>
          <td><?= e($o['payment_method']) ?></td>
          <td><?= e(label_order_status($o['status'])) ?><?= !empty($o['refunded_at']) ? ' / reembolsado' : '' ?></td>
          <td><?= e($o['created_at']) ?></td>
          <td>
            <?php if ($o['status'] === 'paid' && empty($o['refunded_at'])): ?>
              <form method="post" action="<?= base_url('admin/pedidos/reembolso') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                <input type="hidden" name="reason" value="Reembolso administrativo">
                <button class="btn btn-danger btn-sm" type="submit">Reembolsar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
