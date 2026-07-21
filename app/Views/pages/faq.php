<section class="page-hero"><div class="container"><h1>Perguntas frequentes</h1><p>Respostas sobre bilhetes e a plataforma.</p></div></section>
<section>
  <div class="container faq">
    <?php foreach ($faqs as $faq): ?>
      <details>
        <summary><?= e($faq['question']) ?></summary>
        <p><?= e($faq['answer']) ?></p>
      </details>
    <?php endforeach; ?>
    <details>
      <summary>O que acontece se o espetáculo for cancelado?</summary>
      <p>Nos termos da legislação portuguesa, o cancelamento do espetáculo pelo organizador dá direito ao reembolso do valor do bilhete. Contacte o suporte ou utilize a área de conta para pedir reembolso.</p>
    </details>
    <details>
      <summary>Posso revender o meu bilhete?</summary>
      <p>Não. A revenda não autorizada, especialmente acima do preço original, é proibida. Os bilhetes são nominativos e com QR único de uso único.</p>
    </details>
    <details>
      <summary>Como apresentar uma reclamação ou resolver um litígio?</summary>
      <p>Use o Livro de Reclamações Eletrónico no rodapé do site, o formulário de contacto, ou consulte a página de Resolução Alternativa de Litígios (RAL).</p>
    </details>
    <details>
      <summary>Posso usar a EventTicket-GB como aplicação no telemóvel?</summary>
      <p>Sim. No Android, use “Instalar aplicação”. No iPhone (Safari), Partilhar → “Adicionar ao ecrã principal”. A app abre em ecrã completo.</p>
    </details>
  </div>
</section>
