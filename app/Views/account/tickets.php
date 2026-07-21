<section class="page-hero"><div class="container"><h1>Os meus bilhetes</h1></div></section>
<section>
  <div class="container form-card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Evento</th><th>Tipo</th><th>Lugar</th><th>Código</th><th>PDF</th></tr></thead>
        <tbody>
          <?php foreach ($tickets as $t): ?>
            <tr>
              <td><?= e($t['event_title']) ?><br><small><?= e(format_datetime($t['starts_at'])) ?></small></td>
              <td><?= e($t['ticket_name']) ?></td>
              <td><?= e($t['seat_label'] ?? '—') ?></td>
              <td><code><?= e($t['code']) ?></code><?= $t['used_at'] ? '<br><span class="badge badge-bad">Usado</span>' : '' ?></td>
              <td>
                <a
                  class="btn btn-primary btn-sm"
                  href="<?= e(base_url('bilhete/' . urlencode($t['code']))) ?>"
                  data-ticket-modal
                  data-ticket-code="<?= e($t['code']) ?>"
                ><?= icon('eye') ?><span>Ver</span></a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$tickets): ?><tr><td colspan="5">Ainda sem bilhetes.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
