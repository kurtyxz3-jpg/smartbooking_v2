(() => {
  const modal = document.getElementById('editAdminModal');
  if (!modal) return;

  const fields = {
    id: document.getElementById('edit_admin_id'),
    fullname: document.getElementById('edit_fullname'),
    username: document.getElementById('edit_username'),
    email: document.getElementById('edit_email'),
    phone_number: document.getElementById('edit_phone_number'),
    branch_id: document.getElementById('edit_branch_id'),
    status: document.getElementById('edit_status'),
  };

  const closeModal = () => {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('admin-modal-open');
  };

  document.querySelectorAll('.js-edit-admin').forEach((button) => {
    button.addEventListener('click', () => {
      let administrator;
      try { administrator = JSON.parse(button.dataset.admin || '{}'); }
      catch (_) { return; }
      Object.entries(fields).forEach(([key, input]) => {
        if (input) input.value = administrator[key] ?? '';
      });
      document.getElementById('edit_new_password').value = '';
      document.getElementById('edit_confirm_password').value = '';
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('admin-modal-open');
      window.setTimeout(() => fields.fullname?.focus(), 20);
    });
  });

  document.querySelectorAll('.js-close-admin-modal').forEach((button) => {
    button.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.hidden) closeModal();
  });

  document.getElementById('editAdminForm')?.addEventListener('submit', (event) => {
    const password = document.getElementById('edit_new_password').value;
    const confirmation = document.getElementById('edit_confirm_password').value;
    if (password !== confirmation) {
      event.preventDefault();
      document.getElementById('edit_confirm_password').setCustomValidity('Passwords do not match.');
      document.getElementById('edit_confirm_password').reportValidity();
    }
  });
  document.getElementById('edit_confirm_password')?.addEventListener('input', (event) => {
    event.currentTarget.setCustomValidity('');
  });
})();
