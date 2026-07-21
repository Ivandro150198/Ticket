<h1 style="font-family:var(--font-display);margin-top:0">Newsletter</h1>
<div class="form-card">
  <table>
    <thead><tr><th>E-mail</th><th>Data</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($subscribers as $s): ?>
        <tr>
          <td><?= e($s['email']) ?></td>
          <td><?= e($s['created_at']) ?></td>
          <td>
            <form method="post" action="<?= base_url('admin/newsletter/eliminar') ?>">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit">Remover</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
