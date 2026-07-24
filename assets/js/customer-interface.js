(() => {
  const toggle = document.getElementById('customerMenuToggle');
  const nav = document.getElementById('customerNavigation');

  toggle?.addEventListener('click', () => {
    const open = nav?.classList.toggle('open') || false;
    toggle.setAttribute('aria-expanded', String(open));
  });

  document.addEventListener('click', (event) => {
    if (!nav || !toggle) return;
    if (nav.contains(event.target) || toggle.contains(event.target)) return;

    nav.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  });

  document.querySelectorAll('[data-customer-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      const message =
        button.dataset.customerConfirm
        || 'Are you sure you want to continue?';

      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  const toast = document.querySelector('.customer-toast');
  if (toast) {
    window.setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-8px)';
      window.setTimeout(() => toast.remove(), 250);
    }, 4500);
  }

  const rating = document.querySelector('[data-rating-group]');
  if (rating) {
    rating.querySelectorAll('button').forEach((button) => {
      button.addEventListener('click', () => {
        const value = button.dataset.ratingValue;
        const input = document.getElementById('rating');

        if (input) input.value = value;

        rating.querySelectorAll('button').forEach((item) => {
          item.classList.toggle(
            'active',
            Number(item.dataset.ratingValue) <= Number(value)
          );
        });
      });
    });
  }

  const aiForm = document.getElementById('customerAiForm');
  const aiResults = document.getElementById('customerAiResults');

  aiForm?.addEventListener('submit', (event) => {
    event.preventDefault();

    if (aiResults) {
      aiResults.hidden = false;
      aiResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
})();
