(() => {
  const page = document.querySelector('.inventory-page');
  if (!page) return;
  const endpoint = page.dataset.endpoint;
  const csrf = page.dataset.csrf;
  const toast = document.getElementById('inventoryToast');

  const showToast = (message, success = true) => {
    toast.textContent = message;
    toast.className = `inventory-toast show ${success ? 'success' : 'error'}`;
    window.setTimeout(() => toast.classList.remove('show'), 3200);
  };
  const openModal = (id) => document.getElementById(id)?.showModal();
  const closeModal = (dialog) => dialog?.close();

  document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => openModal(button.dataset.modalOpen));
  });
  document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => closeModal(button.closest('dialog')));
  });

  const request = async (data) => {
    data.csrf_token = csrf;
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const result = await response.json().catch(() => ({success: false, message: 'Invalid server response.'}));
    if (!response.ok || !result.success) throw new Error(result.message || 'Request failed.');
    return result;
  };

  document.querySelectorAll('.inventory-modal form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = form.querySelector('[type="submit"], button:not([type])');
      if (submit) submit.disabled = true;
      try {
        const result = await request(Object.fromEntries(new FormData(form).entries()));
        showToast(result.message);
        closeModal(form.closest('dialog'));
        window.setTimeout(() => location.reload(), 500);
      } catch (error) {
        showToast(error.message, false);
      } finally {
        if (submit) submit.disabled = false;
      }
    });
  });

  const search = document.getElementById('inventorySearch');
  const category = document.getElementById('inventoryCategoryFilter');
  const status = document.getElementById('inventoryStatusFilter');
  const rows = [...document.querySelectorAll('#inventoryProductRows tr[data-product]')];
  const resultCount = document.getElementById('inventoryResultCount');
  const emptyState = document.getElementById('inventoryEmptyState');

  const applyFilters = () => {
    const query = search.value.toLowerCase().trim();
    let visible = 0;
    rows.forEach((row) => {
      const match = (!query || row.dataset.search.includes(query))
        && (!category.value || row.dataset.category === category.value)
        && (!status.value || row.dataset.status === status.value);
      row.hidden = !match;
      if (match) visible++;
    });
    resultCount.textContent = `${visible} product${visible === 1 ? '' : 's'}`;
    emptyState.hidden = visible > 0;
  };
  [search, category, status].forEach((control) => control.addEventListener('input', applyFilters));
  document.getElementById('inventoryResetFilters').addEventListener('click', () => {
    search.value = ''; category.value = ''; status.value = ''; applyFilters(); search.focus();
  });

  const productForm = document.getElementById('productForm');
  document.getElementById('inventoryProductRows').addEventListener('click', async (event) => {
    const button = event.target.closest('[data-action]');
    const row = event.target.closest('tr[data-product]');
    if (!button || !row) return;
    const product = JSON.parse(row.dataset.product);

    if (button.dataset.action === 'edit') {
      productForm.reset();
      Object.entries(product).forEach(([key, value]) => {
        const field = productForm.elements.namedItem(key);
        if (field) field.value = value ?? '';
      });
      openModal('productModal');
    }
    if (button.dataset.action === 'stock') {
      const form = document.getElementById('stockForm');
      form.reset();
      form.elements.id.value = product.id;
      document.querySelector('[data-stock-product]').textContent =
        `${product.item_name} · Current stock: ${product.quantity} ${product.unit || 'unit'}`;
      openModal('stockModal');
    }
    if (button.dataset.action === 'archive') {
      if (!confirm(`Remove "${product.item_name}" from active inventory? Its movement history will be preserved.`)) return;
      try {
        const result = await request({action: 'archive_product', id: product.id});
        showToast(result.message);
        row.remove();
        applyFilters();
      } catch (error) { showToast(error.message, false); }
    }
    if (button.dataset.action === 'history') {
      const container = document.getElementById('movementHistory');
      container.innerHTML = '<p class="inventory-loading">Loading movement history…</p>';
      openModal('historyModal');
      try {
        const result = await request({action: 'movement_history', id: product.id});
        const movements = result.data.movements || [];
        container.innerHTML = movements.length ? movements.map((movement) => `
          <article>
            <span class="movement-icon ${movement.movement_type}">${movement.movement_type === 'in' ? '+' : movement.movement_type === 'out' ? '−' : '↔'}</span>
            <div><strong>${movement.movement_type.replace('_', ' ')} · ${movement.quantity}</strong>
            <span>${movement.quantity_before} → ${movement.quantity_after}</span>
            <small>${escapeHtml(movement.note || 'No note')} · ${escapeHtml(movement.recorded_by || 'System')} · ${escapeHtml(movement.created_at)}</small></div>
          </article>`).join('') : '<div class="inventory-empty"><strong>No history yet</strong></div>';
      } catch (error) {
        container.innerHTML = `<div class="inventory-empty"><strong>${escapeHtml(error.message)}</strong></div>`;
      }
    }
  });

  const escapeHtml = (value) => {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
  };
})();
