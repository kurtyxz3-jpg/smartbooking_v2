document.querySelectorAll('[data-password]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.password);
    if (!input) return;

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.textContent = show ? 'Hide' : 'Show';
  });
});

document.querySelectorAll('[data-disabled-provider]').forEach((link) => {
  link.addEventListener('click', (event) => {
    event.preventDefault();

    window.location.href =
      '/smartbooking_v2/auth/oauth-status.php?provider='
      + encodeURIComponent(
          String(link.dataset.disabledProvider || '').toLowerCase()
        );
  });
});
