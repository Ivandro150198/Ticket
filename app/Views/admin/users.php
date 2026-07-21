<h1 style="font-family:var(--font-display);margin-top:0">Utilizadores</h1>
<div class="form-card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e(label_role($u['role'])) ?></td>
            <td>
              <form method="post" action="<?= base_url('admin/utilizadores/role') ?>" style="display:flex;gap:.4rem">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <select name="role">
                  <?php foreach (['cliente','produtor','admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= e(label_role($r)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-ghost btn-sm" type="submit">Guardar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
