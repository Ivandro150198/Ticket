document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('[data-nav]');
  const backdrop = document.querySelector('[data-nav-backdrop]');

  const setNavOpen = (open) => {
    if (!nav || !toggle) return;
    nav.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (backdrop) backdrop.hidden = !open;
  };

  if (toggle && nav) {
    toggle.addEventListener('click', () => setNavOpen(!nav.classList.contains('open')));
    nav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setNavOpen(false));
    });
  }
  if (backdrop) {
    backdrop.addEventListener('click', () => setNavOpen(false));
  }

  const panelToggle = document.querySelector('[data-panel-toggle]');
  const panelSide = document.querySelector('[data-panel-side]');
  const panelBackdrop = document.querySelector('[data-panel-backdrop]');

  const setPanelOpen = (open) => {
    if (!panelSide) return;
    panelSide.classList.toggle('open', open);
    document.body.classList.toggle('panel-open', open);
    if (panelToggle) panelToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (panelBackdrop) panelBackdrop.hidden = !open;
  };

  if (panelToggle && panelSide) {
    panelToggle.addEventListener('click', () => setPanelOpen(!panelSide.classList.contains('open')));
    panelSide.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setPanelOpen(false));
    });
  }
  if (panelBackdrop) {
    panelBackdrop.addEventListener('click', () => setPanelOpen(false));
  }

  const ticketDialog = document.querySelector('[data-ticket-dialog]');
  const ticketFrame = ticketDialog ? ticketDialog.querySelector('[data-ticket-frame]') : null;
  const ticketLabel = ticketDialog ? ticketDialog.querySelector('[data-ticket-label]') : null;
  const ticketDownload = ticketDialog ? ticketDialog.querySelector('[data-ticket-download]') : null;
  const ticketCloseBtn = ticketDialog ? ticketDialog.querySelector('.ticket-modal-x') : null;
  let ticketLastFocus = null;

  const setTicketModal = (open, url, code) => {
    if (!ticketDialog || !ticketFrame) return false;
    url = url || '';
    code = code || '';
    if (open) {
      if (!url) return false;
      ticketLastFocus = document.activeElement;
      if (ticketLabel) ticketLabel.textContent = code ? ('Código ' + code) : '';
      if (ticketDownload) {
        ticketDownload.href = url;
        ticketDownload.setAttribute('download', code ? ('bilhete-' + code + '.pdf') : 'bilhete.pdf');
      }
      ticketDialog.hidden = false;
      document.body.classList.add('ticket-modal-open');
      ticketFrame.removeAttribute('src');
      ticketFrame.src = url;
      if (ticketCloseBtn) ticketCloseBtn.focus();
      return true;
    }
    ticketDialog.hidden = true;
    document.body.classList.remove('ticket-modal-open');
    ticketFrame.src = 'about:blank';
    if (ticketLastFocus && typeof ticketLastFocus.focus === 'function') {
      ticketLastFocus.focus();
    }
    return true;
  };

  document.addEventListener('click', (e) => {
    const openBtn = e.target.closest('[data-ticket-modal]');
    if (openBtn) {
      const url = openBtn.getAttribute('data-ticket-url') || openBtn.getAttribute('href') || '';
      const code = openBtn.getAttribute('data-ticket-code') || '';
      if (setTicketModal(true, url, code)) {
        e.preventDefault();
      }
      return;
    }
    if (e.target.closest('[data-ticket-close]')) {
      setTicketModal(false);
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      setNavOpen(false);
      setPanelOpen(false);
      setTicketModal(false);
    }
  });

  /* Hero 3D — eventos em destaque / datas próximas */
  const hero = document.querySelector('[data-hero-3d]');
  if (hero) {
    const panels = [...hero.querySelectorAll('[data-hero-panel]')];
    const dots = [...hero.querySelectorAll('[data-hero-dot]')];
    const lead = hero.querySelector('[data-hero-lead]');
    const meta = hero.querySelector('[data-hero-meta]');
    const cta = hero.querySelector('[data-hero-cta]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let index = Math.max(0, panels.findIndex((p) => p.classList.contains('is-active')));
    let timer = null;
    let busy = false;

    const setCopy = (panel) => {
      if (!panel) return;
      const title = panel.dataset.title || '';
      const info = panel.dataset.meta || '';
      const href = panel.dataset.href || '';
      const swap = () => {
        if (lead) lead.textContent = title;
        if (meta) meta.textContent = info;
        if (cta && href) {
          cta.setAttribute('href', href);
          cta.textContent = 'Ver este evento';
        }
        lead?.classList.remove('is-swap');
        meta?.classList.remove('is-swap');
      };
      if (reduceMotion) {
        swap();
        return;
      }
      lead?.classList.add('is-swap');
      meta?.classList.add('is-swap');
      window.setTimeout(swap, 280);
    };

    const goTo = (next) => {
      if (!panels.length || busy) return;
      const total = panels.length;
      next = ((next % total) + total) % total;
      if (next === index) return;
      busy = true;
      const current = panels[index];
      const target = panels[next];
      panels.forEach((p) => p.classList.remove('is-leave', 'is-prep'));
      current.classList.remove('is-active');
      current.classList.add('is-leave');
      target.classList.add('is-prep');
      // force reflow for transition restart
      void target.offsetWidth;
      target.classList.remove('is-prep');
      target.classList.add('is-active');
      target.setAttribute('aria-hidden', 'false');
      current.setAttribute('aria-hidden', 'true');
      dots.forEach((d, i) => {
        d.classList.toggle('is-active', i === next);
        d.setAttribute('aria-selected', i === next ? 'true' : 'false');
      });
      setCopy(target);
      index = next;
      window.setTimeout(() => {
        current.classList.remove('is-leave');
        busy = false;
      }, reduceMotion ? 0 : 1050);
    };

    const play = () => {
      stop();
      if (panels.length < 2 || reduceMotion) return;
      timer = window.setInterval(() => goTo(index + 1), 5200);
    };
    const stop = () => {
      if (timer) window.clearInterval(timer);
      timer = null;
    };

    hero.querySelector('[data-hero-prev]')?.addEventListener('click', () => {
      goTo(index - 1);
      play();
    });
    hero.querySelector('[data-hero-next]')?.addEventListener('click', () => {
      goTo(index + 1);
      play();
    });
    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        goTo(Number(dot.dataset.heroDot || 0));
        play();
      });
    });

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', play);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) stop();
      else play();
    });

    play();
  }

  document.querySelectorAll('[data-add-type-row]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const wrap = document.querySelector('[data-types]');
      if (!wrap) return;
      const row = document.createElement('div');
      row.className = 'form-row type-row';
      row.innerHTML = `
        <input type="hidden" name="type_id[]" value="0">
        <label>Nome<input name="type_name[]" required placeholder="Geral"></label>
        <label>Preço<input name="type_price[]" type="number" step="0.01" min="0" required></label>
        <label>Promoção<input name="type_promo[]" type="number" step="0.01" min="0"></label>
        <label>Disponíveis<input name="type_stock[]" type="number" min="0" value="100" required></label>
      `;
      wrap.appendChild(row);
    });
  });

  const cookieBanner = document.querySelector('[data-cookie-banner]');
  if (cookieBanner) {
    const key = 'etgb_cookie_consent';
    const saved = localStorage.getItem(key);
    if (!saved) cookieBanner.hidden = false;
    cookieBanner.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
      localStorage.setItem(key, 'accepted');
      cookieBanner.hidden = true;
    });
    cookieBanner.querySelector('[data-cookie-reject]')?.addEventListener('click', () => {
      localStorage.setItem(key, 'essential');
      cookieBanner.hidden = true;
    });
  }

  /* PWA — instalar como app no telemóvel */
  const appBase = document.body?.dataset?.appBase || '';
  if ('serviceWorker' in navigator && appBase) {
    navigator.serviceWorker.register(appBase + '/sw.js', { scope: appBase + '/' }).catch(() => {});
  }

  let deferredPrompt = null;
  const pwaBox = document.querySelector('[data-pwa-install]');
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (!isStandalone && pwaBox && localStorage.getItem('etgb_pwa_dismiss') !== '1') {
      pwaBox.hidden = false;
    }
  });

  pwaBox?.querySelector('[data-pwa-accept]')?.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    pwaBox.hidden = true;
  });
  pwaBox?.querySelector('[data-pwa-dismiss]')?.addEventListener('click', () => {
    localStorage.setItem('etgb_pwa_dismiss', '1');
    pwaBox.hidden = true;
  });

  // iOS: mostrar dica se ainda não instalado
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  if (isIos && !isStandalone && pwaBox && localStorage.getItem('etgb_pwa_dismiss') !== '1') {
    const copy = pwaBox.querySelector('p');
    if (copy) {
      copy.textContent = 'No Safari: partilhar → “Adicionar ao ecrã principal” para abrir como app.';
    }
    pwaBox.hidden = false;
    const accept = pwaBox.querySelector('[data-pwa-accept]');
    if (accept) accept.hidden = true;
  }
});
