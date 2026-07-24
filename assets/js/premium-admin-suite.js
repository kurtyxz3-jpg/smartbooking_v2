(() => {
  document.querySelectorAll('[data-premium-modal-open]').forEach((button) => {
    button.addEventListener('click', () => {
      const modal = document.getElementById(button.dataset.premiumModalOpen);
      if (!modal) return;

      const payload = button.dataset.payload
        ? JSON.parse(button.dataset.payload)
        : null;

      if (payload) {
        modal.querySelectorAll('[data-field]').forEach((field) => {
          const key = field.dataset.field;
          if (!(key in payload)) return;

          if (field.type === 'checkbox') {
            field.checked = Number(payload[key]) === 1;
          } else {
            field.value = payload[key] ?? '';
          }
        });
      } else {
        modal.querySelector('form')?.reset();
        modal.querySelectorAll('[data-field="id"]').forEach((field) => {
          field.value = '';
        });
      }

      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    });
  });

  document.querySelectorAll('[data-premium-modal-close]').forEach((button) => {
    button.addEventListener('click', () => {
      const modal = button.closest('.premium-admin-modal');
      modal?.classList.remove('open');
      modal?.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    });
  });

  document.querySelectorAll('[data-premium-confirm]').forEach((element) => {
    element.addEventListener('submit', (event) => {
      const message =
        element.dataset.premiumConfirm
        || 'Are you sure you want to continue?';

      if (!window.confirm(message)) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-live-search]').forEach((input) => {
    const target = document.getElementById(input.dataset.liveSearch);
    if (!target) return;

    const rows = [...target.querySelectorAll('[data-search-row]')];

    input.addEventListener('input', () => {
      const query = input.value.toLowerCase().trim();

      rows.forEach((row) => {
        const text = (row.dataset.searchRow || '').toLowerCase();
        row.hidden = query !== '' && !text.includes(query);
      });
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    document.querySelectorAll('.premium-admin-modal.open').forEach((modal) => {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    });
  });
})();
