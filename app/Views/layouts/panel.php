<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title ?? 'Painel') ?> · EventTicket-GB</title>
  <meta name="theme-color" content="#c45c26">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="EventTicket">
  <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
  <link rel="apple-touch-icon" href="<?= asset('img/icon-180.png') ?>">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body data-app-base="<?= e(rtrim((string) config('url', ''), '/')) ?>">
  <div class="panel-layout">
    <header class="panel-topbar">
      <button class="icon-btn menu-toggle" type="button" data-panel-toggle aria-label="Abrir menu" title="Menu" aria-expanded="false">
        <?= icon('menu') ?>
      </button>
      <a class="brand" href="<?= base_url() ?>">Event<span>Ticket</span>-GB</a>
      <a class="icon-btn icon-btn-ghost" href="<?= base_url() ?>" aria-label="Site" title="Site"><?= icon('site') ?></a>
    </header>
    <aside class="panel-side" data-panel-side>
      <a class="brand panel-side-brand" href="<?= base_url() ?>">Event<span>Ticket</span>-GB</a>
      <nav>
        <?php if (auth_role() === 'admin'): ?>
          <a href="<?= base_url('admin') ?>"><?= icon('admin') ?><span>Painel admin</span></a>
          <a href="<?= base_url('admin/eventos') ?>"><?= icon('events') ?><span>Gestão de eventos</span></a>
          <a href="<?= base_url('admin/pedidos') ?>"><?= icon('ticket') ?><span>Pedidos</span></a>
          <a href="<?= base_url('admin/pedidos/export') ?>"><?= icon('download') ?><span>Exportar CSV</span></a>
          <a href="<?= base_url('admin/cupoes') ?>"><?= icon('plus') ?><span>Cupões</span></a>
          <a href="<?= base_url('admin/utilizadores') ?>"><?= icon('user') ?><span>Utilizadores</span></a>
          <a href="<?= base_url('admin/parceiros') ?>"><?= icon('services') ?><span>Parceiros</span></a>
          <a href="<?= base_url('admin/newsletter') ?>"><?= icon('contact') ?><span>Newsletter</span></a>
          <a href="<?= base_url('admin/mensagens') ?>"><?= icon('contact') ?><span>Mensagens</span></a>
          <a href="<?= base_url('admin/reclamacoes') ?>"><?= icon('help') ?><span>Reclamações</span></a>
          <a href="<?= base_url('admin/auditoria') ?>"><?= icon('eye') ?><span>Auditoria</span></a>
          <a href="<?= base_url('admin/backup') ?>"><?= icon('download') ?><span>Cópia de segurança</span></a>
        <?php endif; ?>
        <?php if (in_array(auth_role(), ['produtor', 'admin'], true)): ?>
          <a href="<?= base_url('produtor') ?>"><?= icon('producer') ?><span>Painel produtor</span></a>
          <a href="<?= base_url('produtor/eventos') ?>"><?= icon('events') ?><span>Gestão de eventos</span></a>
          <a href="<?= base_url('produtor/eventos/novo') ?>"><?= icon('plus') ?><span>Inserir evento</span></a>
          <a href="<?= base_url('produtor/vendas') ?>"><?= icon('ticket') ?><span>Vendas</span></a>
          <a href="<?= base_url('produtor/vendas/export') ?>"><?= icon('download') ?><span>Exportar vendas</span></a>
          <a href="<?= base_url('produtor/validar') ?>"><?= icon('eye') ?><span>Validar QR</span></a>
          <a href="<?= base_url('produtor/validar-offline') ?>"><?= icon('search') ?><span>Validação sem rede</span></a>
        <?php endif; ?>
        <a href="<?= base_url() ?>"><?= icon('site') ?><span>Site</span></a>
        <form action="<?= base_url('logout') ?>" method="post">
          <?= csrf_field() ?>
          <button class="btn btn-ghost btn-sm nav-icon-btn" type="submit" style="width:100%;margin-top:.5rem"><?= icon('logout') ?><span>Sair</span></button>
        </form>
      </nav>
    </aside>
    <div class="panel-backdrop" data-panel-backdrop hidden></div>
    <div class="panel-main">
      <?php if ($msg = flash('success')): ?>
        <div class="flash flash-success"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="flash flash-error"><?= e($msg) ?></div>
      <?php endif; ?>
      <?= $content ?>
    </div>
  </div>
  <script src="<?= asset('js/app.js') ?>"></script>
  <?= $panelScripts ?? '' ?>
</body>
</html>
