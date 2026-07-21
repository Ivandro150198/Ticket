<h1 style="font-family:var(--font-display);margin-top:0">Auditoria</h1>
<div class="form-card table-wrap">
  <table>
    <thead><tr><th>Quando</th><th>Utilizador</th><th>Ação</th><th>Entidade</th><th>IP</th><th>Detalhes</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['created_at']) ?></td>
          <td><?= e($l['user_name'] ?? '—') ?></td>
          <td><?= e($l['action']) ?></td>
          <td><?= e(($l['entity'] ?? '') . ' ' . ($l['entity_id'] ?? '')) ?></td>
          <td><?= e($l['ip'] ?? '') ?></td>
          <td><small><?= e($l['meta'] ?? '') ?></small></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
