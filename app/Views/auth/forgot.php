<section class="page-hero"><div class="container"><h1>Recuperar palavra-passe</h1><p>Indique o seu e-mail. Em ambiente local o link fica em <code>storage/mail.log</code>.</p></div></section>
<section>
  <div class="container">
    <div class="form-card auth-wrap">
      <form class="form-grid" method="post" action="<?= base_url('recuperar') ?>">
        <?= csrf_field() ?>
        <label>E-mail<input type="email" name="email" required></label>
        <button class="btn btn-primary" type="submit">Enviar link</button>
      </form>
    </div>
  </div>
</section>
