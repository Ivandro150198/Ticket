<section class="page-hero"><div class="container"><h1>Criar conta</h1><p>Registe-se para comprar bilhetes e acompanhar pedidos.</p></div></section>
<section>
  <div class="container">
    <div class="form-card auth-wrap">
      <form class="form-grid" method="post" action="<?= base_url('registo') ?>">
        <?= csrf_field() ?>
        <label>Nome<input type="text" name="name" required value="<?= old('name') ?>"></label>
        <label>E-mail<input type="email" name="email" required value="<?= old('email') ?>"></label>
        <label>Telefone<input type="text" name="phone" value="<?= old('phone') ?>"></label>
        <label>Palavra-passe<input type="password" name="password" required minlength="6"></label>
        <label class="check" style="display:flex;gap:.5rem;align-items:flex-start;color:var(--sand-dim)">
          <input type="checkbox" name="accept" value="1" required>
          Aceito os <a href="<?= base_url('termos') ?>">Termos de Serviço</a> e a <a href="<?= base_url('privacidade') ?>">Política de Privacidade</a>.
        </label>
        <button class="btn btn-primary" type="submit">Criar conta</button>
      </form>
      <p style="margin-top:1rem;color:var(--sand-dim)"><a href="<?= base_url('login') ?>">Já tem conta? Entrar</a></p>
    </div>
  </div>
</section>
