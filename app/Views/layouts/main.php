<?php
$seo = $seo ?? seo_defaults(['title' => $title ?? null]);
$jsonldBlocks = $seo['jsonld'] ?? [];
if (!is_array($jsonldBlocks) || $jsonldBlocks === []) {
    $jsonldBlocks = [organization_jsonld()];
} elseif (!isset($jsonldBlocks[0])) {
    $jsonldBlocks = [$jsonldBlocks];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($seo['title']) ?></title>
  <meta name="description" content="<?= e($seo['description']) ?>">
  <meta name="keywords" content="<?= e($seo['keywords']) ?>">
  <meta name="robots" content="<?= e($seo['robots']) ?>">
  <meta name="author" content="EventTicket-GB">
  <meta name="theme-color" content="#c45c26">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="EventTicket">
  <meta name="application-name" content="EventTicket-GB">
  <link rel="canonical" href="<?= e($seo['canonical']) ?>">
  <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
  <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
  <link rel="apple-touch-icon" href="<?= asset('img/icon-180.png') ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/icon-180.png') ?>">

  <meta property="og:locale" content="pt_PT">
  <meta property="og:type" content="<?= e($seo['type']) ?>">
  <meta property="og:site_name" content="EventTicket-GB">
  <meta property="og:title" content="<?= e($seo['title']) ?>">
  <meta property="og:description" content="<?= e($seo['description']) ?>">
  <meta property="og:url" content="<?= e($seo['canonical']) ?>">
  <meta property="og:image" content="<?= e($seo['image']) ?>">
  <meta property="og:image:alt" content="<?= e($seo['title']) ?>">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($seo['title']) ?>">
  <meta name="twitter:description" content="<?= e($seo['description']) ?>">
  <meta name="twitter:image" content="<?= e($seo['image']) ?>">

  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <?php foreach ($jsonldBlocks as $block): ?>
  <script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
  <?php endforeach; ?>
</head>
<body data-app-base="<?= e(rtrim((string) config('url', ''), '/')) ?>">
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="<?= base_url() ?>">Event<span>Ticket</span>-GB</a>
      <div class="header-actions">
        <a class="icon-btn header-cart" href="<?= base_url('carrinho') ?>" aria-label="Carrinho" title="Carrinho">
          <?= icon('cart') ?>
          <span class="cart-badge"><?= cart_count() ?></span>
        </a>
        <button class="icon-btn menu-toggle" type="button" data-menu-toggle aria-label="Abrir menu" title="Menu" aria-expanded="false" aria-controls="site-nav">
          <?= icon('menu') ?>
        </button>
      </div>
      <nav class="nav" id="site-nav" data-nav aria-label="Principal">
        <a href="<?= base_url() ?>"><?= icon('home') ?><span>Início</span></a>
        <a href="<?= base_url('eventos') ?>"><?= icon('events') ?><span>Eventos</span></a>
        <a href="<?= base_url('servicos') ?>"><?= icon('services') ?><span>Serviços</span></a>
        <a href="<?= base_url('contacto') ?>"><?= icon('contact') ?><span>Contacto</span></a>
        <a href="<?= base_url('faq') ?>"><?= icon('help') ?><span>Ajuda</span></a>
        <?php if (auth_check()): ?>
          <a href="<?= base_url('conta') ?>"><?= icon('user') ?><span>Conta</span></a>
          <?php if (auth_role() === 'admin'): ?>
            <a href="<?= base_url('admin') ?>"><?= icon('admin') ?><span>Admin</span></a>
          <?php endif; ?>
          <?php if (in_array(auth_role(), ['produtor', 'admin'], true)): ?>
            <a href="<?= base_url('produtor') ?>"><?= icon('producer') ?><span>Produtor</span></a>
          <?php endif; ?>
          <form action="<?= base_url('logout') ?>" method="post" class="nav-logout">
            <?= csrf_field() ?>
            <button class="icon-btn icon-btn-ghost" type="submit" aria-label="Sair" title="Sair"><?= icon('logout') ?></button>
          </form>
        <?php else: ?>
          <a class="icon-link" href="<?= base_url('login') ?>" title="Entrar"><?= icon('login') ?><span></span></a>
          <a class="icon-link" href="<?= base_url('registo') ?>" title="Criar conta"><?= icon('user') ?><span></span></a>
        <?php endif; ?>
        <a class="nav-cta nav-cart-desktop" href="<?= base_url('carrinho') ?>" aria-label="Carrinho" title="Carrinho">
          <?= icon('cart') ?>
          <span class="nav-cta-label"></span>
          <span class="cart-badge"><?= cart_count() ?></span>
        </a>
      </nav>
    </div>
  </header>
  <div class="nav-backdrop" data-nav-backdrop hidden></div>

  <?php if ($msg = flash('success')): ?>
    <div class="container flash-wrap"><div class="flash flash-success"><?= e($msg) ?></div></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="container flash-wrap"><div class="flash flash-error"><?= e($msg) ?></div></div>
  <?php endif; ?>

  <main id="conteudo">
    <?= $content ?>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <h4>EventTicket-GB</h4>
        <p>O seu bilhete para todo o tipo de eventos. Soluções para evitar falsificações, com autenticação e verificação por código QR.</p>
      </div>
      <div>
        <h4>+ Links</h4>
        <ul>
          <li><a href="<?= base_url('postos-de-venda') ?>">Postos de Venda</a></li>
          <li><a href="<?= base_url('termos') ?>">Termos e Condições</a></li>
          <li><a href="<?= base_url('privacidade') ?>">Política de Privacidade</a></li>
          <li><a href="<?= base_url('cookies') ?>">Política de Cookies</a></li>
          <li><a href="<?= base_url('resolucao-litigios') ?>">Resolução de Litígios (RAL)</a></li>
          <li><a href="<?= base_url('livro-de-reclamacoes') ?>">Livro de Reclamações</a></li>
          <li><a href="<?= base_url('servicos') ?>">Produtores de Evento</a></li>
          <li><a href="<?= base_url('sitemap.xml') ?>">Sitemap</a></li>
        </ul>
      </div>
      <div>
        <h4>Ajuda</h4>
        <ul>
          <li>Tel.: 219 210 273</li>
          <li><a href="<?= base_url('contacto') ?>">Formulário de Contacto</a></li>
          <li><a href="<?= base_url('faq') ?>">Ajuda / FAQ</a></li>
          <li><a href="<?= base_url('resolucao-litigios') ?>">RAL / Arbitragem</a></li>
        </ul>
      </div>
      <div>
        <h4>Pagamentos</h4>
        <p>PT: MB Way · Multibanco · Cartão<br>GW: Orange Money · Transferência UEMOA · Cartão<br><small>(pagamento simulado nesta demonstração)</small></p>
      </div>
    </div>
    <div class="container footer-bottom">
      <span>© <?= date('Y') ?> EventTicket-GB — Todos os direitos reservados.</span>
      <span>Bilhetes digitais com QR para Portugal e Guiné-Bissau</span>
    </div>
    <div class="container footer-legal-bar">
      <a class="footer-complaints" href="<?= base_url('livro-de-reclamacoes') ?>" title="Livro de Reclamações Eletrónico">
        <?= icon('help') ?>
        <span>Livro de Reclamações Eletrónico</span>
      </a>
      <a href="<?= base_url('resolucao-litigios') ?>">RAL</a>
      <a href="<?= base_url('privacidade') ?>">RGPD</a>
    </div>
  </footer>

  <div class="cookie-banner" data-cookie-banner hidden>
    <div class="cookie-banner-inner">
      <p>Utilizamos cookies essenciais ao funcionamento do site. Ao continuar, aceita a nossa <a href="<?= base_url('cookies') ?>">política de cookies</a> e <a href="<?= base_url('privacidade') ?>">privacidade</a>.</p>
      <div class="cookie-banner-actions">
        <button type="button" class="btn btn-ghost btn-sm" data-cookie-reject>Só essenciais</button>
        <button type="button" class="btn btn-primary btn-sm" data-cookie-accept>Aceitar</button>
      </div>
    </div>
  </div>
  <div class="pwa-install" data-pwa-install hidden>
    <div class="pwa-install-inner">
      <div>
        <strong>Instalar EventTicket-GB</strong>
        <p>Use como aplicação no telemóvel — ecrã completo e ícone na home.</p>
      </div>
      <div class="pwa-install-actions">
        <button type="button" class="btn btn-ghost btn-sm" data-pwa-dismiss>Agora não</button>
        <button type="button" class="btn btn-primary btn-sm" data-pwa-accept>Instalar</button>
      </div>
    </div>
  </div>
  <div class="ticket-modal" data-ticket-dialog hidden>
    <div class="ticket-modal-backdrop" data-ticket-close tabindex="-1"></div>
    <div class="ticket-modal-panel" role="dialog" aria-modal="true" aria-labelledby="ticket-modal-title">
      <header class="ticket-modal-head">
        <div>
          <h2 id="ticket-modal-title">Bilhete</h2>
          <p class="ticket-modal-code" data-ticket-label></p>
        </div>
        <button type="button" class="ticket-modal-x" data-ticket-close aria-label="Fechar" title="Fechar"><?= icon('close') ?></button>
      </header>
      <div class="ticket-modal-body">
        <iframe data-ticket-frame title="Pré-visualização do bilhete"></iframe>
      </div>
      <footer class="ticket-modal-foot">
        <a class="btn btn-ghost btn-sm" data-ticket-download href="#" download><?= icon('download') ?><span>Descarregar</span></a>
        <button type="button" class="btn btn-primary btn-sm" data-ticket-close><?= icon('close') ?><span>Fechar</span></button>
      </footer>
    </div>
  </div>
  <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
