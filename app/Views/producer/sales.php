<div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center">
  <h1 style="font-family:var(--font-display);margin:0">Vendas</h1>
  <a class="btn btn-ghost" href="<?= base_url('produtor/vendas/export') ?>">Exportar CSV</a>
</div>
<div class="form-card" style="margin-top:1rem">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Evento</th><th>Comprador</th><th>Total</th><th>Pagamento</th><th>Data</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= e($o['event_title']) ?></td>
            <td><?= e($o['buyer_name']) ?><br><small><?= e($o['buyer_email']) ?></small></td>
            <td><?= money((float)$o['total']) ?></td>
            <td><?= e(label_payment_method($o['payment_method'] ?? null)) ?> · <?= e(label_order_status($o['status'])) ?></td>
            <td><?= e($o['created_at']) ?></td>
            <td><a href="<?= base_url('pedido/' . $o['id']) ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
          <tr><td colspan="7">Ainda sem vendas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
