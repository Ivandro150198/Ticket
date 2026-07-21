<section class="page-hero"><div class="container"><h1>Contacto</h1><p>Tel.: 219 210 273 · Envie-nos uma mensagem.</p></div></section>
<section>
  <div class="container">
    <div class="form-card" style="max-width:640px">
      <form class="form-grid" method="post" action="<?= base_url('contacto') ?>">
        <?= csrf_field() ?>
        <div class="form-row">
          <label>Nome<input type="text" name="name" required></label>
          <label>E-mail<input type="email" name="email" required></label>
        </div>
        <label>Assunto<input type="text" name="subject" required></label>
        <label>Mensagem<textarea name="message" required></textarea></label>
        <button class="btn btn-primary" type="submit">Enviar</button>
      </form>
    </div>
  </div>
</section>
