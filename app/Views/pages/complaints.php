<section class="page-hero">
  <div class="container">
    <h1>Livro de Reclamações</h1>
    <p>Registe a sua reclamação. Em ambiente físico utilize também o livro oficial disponível no estabelecimento.</p>
  </div>
</section>
<section>
  <div class="container form-card" style="max-width:720px">
    <p style="color:var(--sand-dim)">Acesso visível ao Livro de Reclamações Eletrónico, exigido a fornecedores online em Portugal. Pode também utilizar o portal oficial: <a href="https://www.livroreclamacoes.pt" target="_blank" rel="noopener">livroreclamacoes.pt</a>. Em litígios de consumo, veja <a href="<?= base_url('resolucao-litigios') ?>">RAL</a>.</p>
    <form class="form-grid" method="post" action="<?= base_url('livro-de-reclamacoes') ?>">
      <?= csrf_field() ?>
      <div class="form-row">
        <label>Nome<input name="name" required></label>
        <label>E-mail<input type="email" name="email" required></label>
      </div>
      <div class="form-row">
        <label>NIF<input name="nif"></label>
        <label>Assunto<input name="subject" required></label>
      </div>
      <label>Reclamação<textarea name="message" required></textarea></label>
      <button class="btn btn-primary" type="submit">Submeter reclamação</button>
    </form>
  </div>
</section>
