<section class="page-hero">
  <div class="container">
    <h1>Pedido #<?= (int)$order['id'] ?></h1>
    <p>Estado: <span class="badge badge-ok"><?= e(label_order_status($order['status'])) ?></span> · <?= e(label_payment_method($order['payment_method'] ?? null)) ?> · <?= e($order['created_at']) ?></p>
  </div>
</section>
<section>
  <div class="container">
    <div class="form-card" style="margin-bottom:1rem">
      <p><strong><?= e($order['buyer_name']) ?></strong> · <?= e($order['buyer_email']) ?></p>
      <?php $cur = $order['currency'] ?? 'EUR'; ?>
      <p><?= e(label_country($order['country'] ?? 'PT')) ?> · <?= e($cur) ?></p>
      <?php if (!empty($order['discount']) && (float)$order['discount'] > 0): ?>
        <p>Subtotal: <?= money((float)$order['total'], $cur) ?> · Desconto: -<?= money((float)$order['discount'], $cur) ?></p>
      <?php endif; ?>
      <p>Total: <?= money(max(0, (float)$order['total'] - (float)($order['discount'] ?? 0)), $cur) ?></p>
      <?php if (!empty($order['payment_ref'])): ?><p>Ref. pagamento: <code><?= e($order['payment_ref']) ?></code></p><?php endif; ?>
      <?php if (($order['status'] ?? '') === 'paid'): ?>
        <p style="margin-top:1rem">
          <a class="btn btn-ghost btn-sm" href="<?= base_url('pedido/' . (int)$order['id'] . '/fatura') ?>"><?= icon('download') ?><span>Fatura / comprovativo</span></a>
        </p>
      <?php endif; ?>
    </div>
    <div class="form-card">
      <h2 style="font-family:var(--font-display);margin-top:0">Os seus bilhetes</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Evento</th><th>Tipo</th><th>Código</th><th>PDF</th></tr></thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
              <tr>
                <td><?= e($t['event_title']) ?><br><small><?= e(format_datetime($t['starts_at'])) ?></small></td>
                <td><?= e($t['ticket_name']) ?></td>
                <td><code><?= e($t['code']) ?></code></td>
                <td>
                  <a
                    class="btn btn-sm btn-primary"
                    href="<?= e(base_url('bilhete/' . urlencode($t['code']))) ?>"
                    data-ticket-modal
                    data-ticket-code="<?= e($t['code']) ?>"
                  ><?= icon('eye') ?><span>Ver bilhete</span></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p style="color:var(--sand-dim);margin-top:1rem">Guarde o bilhete no telemóvel e apresente o QR na porta do evento.</p>
    </div>
  </div>
</section>
