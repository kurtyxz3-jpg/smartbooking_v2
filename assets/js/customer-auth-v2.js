(() => {
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.dataset.passwordToggle;
      const input = document.getElementById(targetId);

      if (!input) return;

      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.textContent = show ? 'Hide' : 'Show';
      button.setAttribute(
        'aria-label',
        show ? 'Hide password' : 'Show password'
      );
    });
  });

  document.querySelectorAll('[data-social-disabled]').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();

      const provider = link.dataset.socialDisabled || 'Social';
      alert(
        `${provider} login is ready for configuration. `
        + 'Add the provider client ID and secret in config/oauth.php.'
      );
    });
  });

  document.querySelectorAll('[data-modal-open]').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();

      const modal = document.getElementById(link.dataset.modalOpen);

      if (!modal) return;

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => {
      const modal = button.closest('.customer-auth-modal');

      modal?.classList.remove('is-open');
      modal?.setAttribute('aria-hidden', 'true');
    });
  });

  document.querySelectorAll('.customer-auth-modal').forEach((modal) => {
    modal.addEventListener('click', (event) => {
      if (event.target !== modal) return;

      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    });
  });
})();
