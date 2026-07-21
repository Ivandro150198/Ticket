<h1 style="font-family:var(--font-display);margin-top:0">Validação sem rede</h1>
<p style="color:var(--sand-dim)">Guarde esta página no ecrã inicial. Sem ligação, pode marcar códigos localmente; ao voltar a ter rede, sincronize.</p>
<div class="form-card validate-box">
  <label>Código
    <input type="text" id="offline-code" placeholder="ABCD1234-EF56">
  </label>
  <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap">
    <button class="btn btn-primary" type="button" id="offline-check">Validar localmente</button>
    <button class="btn btn-ghost" type="button" id="offline-sync">Sincronizar com servidor</button>
  </div>
  <div id="offline-result" class="validate-result" style="display:none;margin-top:1rem"></div>
  <h3 style="margin-top:1.5rem">Fila local</h3>
  <ul id="offline-queue" style="color:var(--sand-dim)"></ul>
</div>
<script>
(function(){
  const KEY = 'etgb_offline_used';
  const QUEUE = 'etgb_offline_queue';
  const result = document.getElementById('offline-result');
  const queueEl = document.getElementById('offline-queue');
  function load(k){ try { return JSON.parse(localStorage.getItem(k)||'[]'); } catch(e){ return []; } }
  function save(k,v){ localStorage.setItem(k, JSON.stringify(v)); }
  function renderQueue(){
    const q = load(QUEUE);
    queueEl.innerHTML = q.length ? q.map(c => '<li>'+c+'</li>').join('') : '<li>Vazia</li>';
  }
  document.getElementById('offline-check').onclick = () => {
    const code = document.getElementById('offline-code').value.trim().toUpperCase().replace(/^ETGB:/,'');
    if (!code) return;
    const used = load(KEY);
    result.style.display = 'block';
    if (used.includes(code)) {
      result.className = 'validate-result bad';
      result.textContent = 'Já validado sem rede: ' + code;
      return;
    }
    used.push(code);
    save(KEY, used);
    const q = load(QUEUE); q.push(code); save(QUEUE, q);
    result.className = 'validate-result ok';
    result.textContent = 'Marcado sem rede: ' + code;
    renderQueue();
  };
  document.getElementById('offline-sync').onclick = async () => {
    const q = load(QUEUE);
    if (!q.length) { alert('Nada para sincronizar'); return; }
    let ok = 0;
    for (const code of q) {
      const body = new FormData();
      body.append('_csrf', '<?= e(csrf_token()) ?>');
      body.append('code', code);
      body.append('ajax', '1');
      try {
        const res = await fetch('<?= base_url('produtor/validar') ?>', { method:'POST', body, credentials:'same-origin' });
        const data = await res.json();
        if (data.ok) ok++;
      } catch(e) {}
    }
    save(QUEUE, []);
    renderQueue();
    alert('Sincronizados com sucesso: ' + ok);
  };
  renderQueue();
})();
</script>
