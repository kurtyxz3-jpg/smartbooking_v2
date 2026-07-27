(() => {
  const dialog = document.getElementById('branchDialog');
  if (!dialog) return;
  const field = id => document.getElementById(id);
  const reset = () => {
    dialog.querySelector('form').reset();
    field('branchId').value = '';
    field('branchOpening').value = '09:00';
    field('branchClosing').value = '20:00';
    field('branchGcashQrPath').value = '';
    field('branchGcashMerchant').value = '';
    field('branchGcashNumber').value = '';
    field('branchDialogTitle').textContent = 'Add Branch';
  };
  document.querySelector('[data-branch-open]')?.addEventListener('click', () => {
    reset(); dialog.showModal();
  });
  document.querySelectorAll('[data-branch-close]').forEach(button =>
    button.addEventListener('click', () => dialog.close())
  );
  document.querySelectorAll('[data-branch-edit]').forEach(button =>
    button.addEventListener('click', () => {
      const data = JSON.parse(button.dataset.branchEdit);
      field('branchId').value = data.id || '';
      field('branchCode').value = data.branch_code || '';
      field('branchName').value = data.branch_name || '';
      field('branchAddress').value = data.address || '';
      field('branchPhone').value = data.contact_number || '';
      field('branchEmail').value = data.email || '';
      field('branchOpening').value = (data.opening_time || '09:00').slice(0,5);
      field('branchClosing').value = (data.closing_time || '20:00').slice(0,5);
      field('branchGcashQrPath').value = data.gcash_qr_path || '';
      field('branchGcashMerchant').value = data.gcash_merchant_name || '';
      field('branchGcashNumber').value = data.gcash_number || '';
      field('branchDialogTitle').textContent = 'Edit Branch';
      dialog.showModal();
    })
  );
})();
