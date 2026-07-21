<section class="page-hero"><div class="container"><h1>Nova palavra-passe</h1></div></section>
<section>
  <div class="container">
    <div class="form-card auth-wrap">
      <form class="form-grid" method="post" action="<?= base_url('repor-senha') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label>Nova palavra-passe<input type="password" name="password" required minlength="6"></label>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </form>
    </div>
  </div>
</section>
