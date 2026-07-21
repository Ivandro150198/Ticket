<section class="page-hero"><div class="container"><h1>Entrar</h1><p>Aceda à sua conta EventTicket-GB.</p></div></section>
<section>
  <div class="container">
    <div class="form-card auth-wrap">
      <form class="form-grid" method="post" action="<?= base_url('login') ?>">
        <?= csrf_field() ?>
        <label>E-mail<input type="email" name="email" required value="<?= old('email') ?>"></label>
        <label>Palavra-passe<input type="password" name="password" required></label>
        <label class="check" style="display:flex;gap:.5rem;align-items:center;color:var(--sand-dim)">
          <input type="checkbox" name="remember" value="1"> Lembrar-me
        </label>
        <button class="btn btn-primary" type="submit">Entrar</button>
      </form>
      <p style="margin-top:1rem;color:var(--sand-dim)">
        <a href="<?= base_url('recuperar') ?>">Esqueceu a senha?</a> ·
        <a href="<?= base_url('registo') ?>">Criar conta</a>
      </p>
    </div>
  </div>
</section>
