<h1 style="font-family:var(--font-display);margin-top:0">Cupões</h1>
<div class="form-card" style="margin-bottom:1rem">
  <form class="form-row" method="post" action="<?= base_url('admin/cupoes') ?>">
    <?= csrf_field() ?>
    <label>Código<input name="code" required></label>
    <label>Tipo
      <select name="type"><option value="percent">%</option><option value="fixed">€ fixo</option></select>
    </label>
    <label>Valor<input name="value" type="number" step="0.01" required></label>
    <label>Máx. usos<input name="max_uses" type="number"></label>
    <label>Mín. total<input name="min_total" type="number" step="0.01" value="0"></label>
    <label>Expira<input name="expires_at" type="datetime-local"></label>
    <label style="display:flex;align-items:end"><button class="btn btn-primary" type="submit">Criar</button></label>
  </form>
</div>
<div class="form-card">
  <table>
    <thead><tr><th>Código</th><th>Tipo</th><th>Valor</th><th>Usos</th><th>Ativo</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($coupons as $c): ?>
        <tr>
          <td><?= e($c['code']) ?></td>
          <td><?= e($c['type']) ?></td>
          <td><?= e($c['value']) ?></td>
          <td><?= (int)$c['used_count'] ?><?= $c['max_uses'] !== null ? ' / ' . (int)$c['max_uses'] : '' ?></td>
          <td><?= $c['active'] ? 'Sim' : 'Não' ?></td>
          <td>
            <form method="post" action="<?= base_url('admin/cupoes/eliminar') ?>">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
