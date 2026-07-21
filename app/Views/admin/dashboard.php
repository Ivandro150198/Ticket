<h1 style="font-family:var(--font-display);margin-top:0">Administração</h1>
<div class="stats">
  <div class="stat">Utilizadores<strong><?= (int)$stats['users'] ?></strong></div>
  <div class="stat">Eventos<strong><?= (int)$stats['events'] ?></strong></div>
  <div class="stat">Pedidos<strong><?= (int)$stats['orders'] ?></strong></div>
  <div class="stat">Receita<strong><?= money((float)$stats['revenue']) ?></strong></div>
</div>
<div class="form-card" style="margin-bottom:1rem">
  <h2 style="font-family:var(--font-display);margin-top:0">Eventos a rever</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Título</th><th>Produtor</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach (array_slice($events, 0, 8) as $ev): ?>
          <tr>
            <td><?= e($ev['title']) ?></td>
            <td><?= e($ev['producer_name']) ?></td>
            <td><?= e(label_event_status($ev['status'])) ?></td>
            <td><a href="<?= base_url('admin/eventos') ?>">Gerir</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="form-card">
  <h2 style="font-family:var(--font-display);margin-top:0">Últimos pedidos</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Comprador</th><th>Total</th><th>Estado</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><a href="<?= base_url('pedido/' . $o['id']) ?>"><?= (int)$o['id'] ?></a></td>
            <td><?= e($o['buyer_name']) ?></td>
            <td><?= money((float)$o['total']) ?></td>
            <td><?= e(label_order_status($o['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
