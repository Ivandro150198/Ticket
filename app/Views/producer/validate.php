<h1 style="font-family:var(--font-display);margin-top:0">Validar bilhete</h1>
<p style="color:var(--sand-dim)">Introduza o código do bilhete ou leia o QR com a câmara.</p>
<div class="form-card validate-box">
  <form class="form-grid" method="post" action="<?= base_url('produtor/validar') ?>" id="validate-form">
    <?= csrf_field() ?>
    <label>Código do bilhete
      <input type="text" name="code" id="ticket-code" required placeholder="ABCD1234-EF56" value="<?= e($_POST['code'] ?? '') ?>">
    </label>
    <button class="btn btn-primary" type="submit">Validar entrada</button>
  </form>
  <div id="qr-reader"></div>
  <button class="btn btn-ghost btn-sm" type="button" id="start-scan" style="margin-top:.75rem">Abrir câmara (QR)</button>

  <?php if ($result): ?>
    <div class="validate-result <?= $result['ok'] ? 'ok' : 'bad' ?>">
      <strong><?= e($result['message']) ?></strong>
      <?php if (!empty($result['ticket'])): ?>
        <p style="margin:.6rem 0 0">
          <?= e($result['ticket']['event_title']) ?><br>
          <?= e($result['ticket']['ticket_name']) ?> · <?= e($result['ticket']['buyer_name']) ?><br>
          Código: <code><?= e($result['ticket']['code']) ?></code>
        </p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
  const btn = document.getElementById('start-scan');
  const input = document.getElementById('ticket-code');
  const form = document.getElementById('validate-form');
  let scanner;
  if (!btn || !window.Html5Qrcode) return;
  btn.addEventListener('click', async () => {
    if (scanner) return;
    scanner = new Html5Qrcode('qr-reader');
    await scanner.start(
      { facingMode: 'environment' },
      { fps: 8, qrbox: 220 },
      (decoded) => {
        let code = decoded.replace(/^ETGB:/, '');
        input.value = code;
        scanner.stop().catch(() => {});
        form.submit();
      }
    );
  });
})();
</script>
