/* Idéias Flamboyant — interações da loja */
(function () {
  'use strict';

  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const base = location.origin;

  function toast(message, ok = true) {
    let el = document.querySelector('.toast');
    if (!el) {
      el = document.createElement('div');
      el.className = 'toast';
      Object.assign(el.style, {
        position: 'fixed', left: '50%', bottom: '28px', transform: 'translateX(-50%)',
        background: ok ? '#1f7a4d' : '#c0341d', color: '#fff', padding: '12px 22px',
        borderRadius: '999px', fontWeight: '600', zIndex: 999, boxShadow: '0 10px 30px rgba(0,0,0,.2)',
        opacity: 0, transition: '.2s', fontSize: '14px'
      });
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.style.background = ok ? '#1f7a4d' : '#c0341d';
    requestAnimationFrame(() => (el.style.opacity = 1));
    clearTimeout(el._t);
    el._t = setTimeout(() => (el.style.opacity = 0), 2600);
  }

  function refreshBadge(count) {
    document.querySelectorAll('[data-cart-badge]').forEach((b) => {
      b.textContent = count;
      b.classList.toggle('hidden', !count);
    });
  }

  async function addToCart(productId, qty = 1) {
    const body = new URLSearchParams({ _token: token, product_id: productId, qty });
    try {
      const res = await fetch(base + '/carrinho/adicionar', {
        method: 'POST',
        headers: { 'X-Requested-With': 'fetch', Accept: 'application/json' },
        body,
      });
      const data = await res.json();
      toast(data.message, data.ok);
      if (data.ok) refreshBadge(data.count);
    } catch (e) {
      toast('Não foi possível adicionar. Tente novamente.', false);
    }
  }

  // Botões "Adicionar" nos cards
  document.addEventListener('click', (ev) => {
    const add = ev.target.closest('.js-add-cart');
    if (add) {
      ev.preventDefault();
      add.disabled = true;
      addToCart(add.dataset.id, 1).finally(() => (add.disabled = false));
      return;
    }

    // Remover item do carrinho
    const rm = ev.target.closest('.js-remove');
    if (rm) {
      ev.preventDefault();
      const body = new URLSearchParams({ _token: token, product_id: rm.dataset.id });
      fetch(base + '/carrinho/remover', {
        method: 'POST',
        headers: { 'X-Requested-With': 'fetch', Accept: 'application/json' },
        body,
      })
        .then((r) => r.json())
        .then((d) => {
          refreshBadge(d.count);
          rm.closest('.cart-row')?.remove();
          toast(d.message, d.ok);
          if (!d.count) location.reload();
        });
      return;
    }

    // Stepper de quantidade (+ / −)
    const step = ev.target.closest('.qty button[data-step]');
    if (step) {
      ev.preventDefault();
      const input = step.parentElement.querySelector('input');
      const max = parseInt(input.dataset.max || '99', 10);
      let val = parseInt(input.value || '1', 10) + parseInt(step.dataset.step, 10);
      val = Math.max(1, Math.min(max || 99, val));
      input.value = val;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  // Formulário de compra na página do produto (intercepta submit)
  document.querySelectorAll('form.js-buy').forEach((form) => {
    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      const id = form.querySelector('[name="product_id"]').value;
      const qty = form.querySelector('[name="qty"]').value || 1;
      const btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;
      addToCart(id, qty).finally(() => (btn.disabled = false));
    });
  });

  // Radios de pagamento no checkout
  document.querySelectorAll('.pay-option input').forEach((r) => {
    const sync = () => {
      document.querySelectorAll('.pay-option').forEach((o) =>
        o.classList.toggle('selected', o.contains(r) && r.checked)
      );
    };
    r.addEventListener('change', () => {
      document.querySelectorAll('.pay-option').forEach((o) => o.classList.remove('selected'));
      if (r.checked) r.closest('.pay-option').classList.add('selected');
    });
    sync();
  });
})();
