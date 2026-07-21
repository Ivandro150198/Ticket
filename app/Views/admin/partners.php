<h1 style="font-family:var(--font-display);margin-top:0">Parceiros</h1>
<div class="form-card" style="margin-bottom:1rem">
  <form class="form-row" method="post" action="<?= base_url('admin/parceiros') ?>">
    <?= csrf_field() ?>
    <label>Nome<input name="name" required></label>
    <label>Website<input name="website" type="url" placeholder="https://"></label>
    <label>Ordem<input name="sort_order" type="number" value="0"></label>
    <label style="display:flex;align-items:end"><button class="btn btn-primary" type="submit">Adicionar</button></label>
  </form>
</div>
<div class="form-card">
  <table>
    <thead><tr><th>Nome</th><th>Website</th><th>Ativo</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($partners as $p): ?>
        <tr>
          <td><?= e($p['name']) ?></td>
          <td><?= e($p['website']) ?></td>
          <td><?= $p['active'] ? 'Sim' : 'Não' ?></td>
          <td style="display:flex;gap:.4rem">
            <form method="post" action="<?= base_url('admin/parceiros/toggle') ?>">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit">Toggle</button>
            </form>
            <form method="post" action="<?= base_url('admin/parceiros/eliminar') ?>">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
