(() => {
  const cfg = window.SMARTBOOKING_PHASE2_AI || {};
  const form = document.getElementById('aiStudioForm');
  const health = document.getElementById('aiHealth');
  const message = document.getElementById('aiMessage');
  const button = document.getElementById('aiGenerateButton');
  const results = document.getElementById('aiResults');
  const grid = document.getElementById('aiResultGrid');
  const modal = document.getElementById('aiImageModal');
  const modalImage = document.getElementById('aiModalImage');
  const modalTitle = document.getElementById('aiModalTitle');
  const modalDownload = document.getElementById('aiModalDownload');
  const modalClose = document.getElementById('aiModalClose');
  const modalBackdrop = document.getElementById('aiModalBackdrop');
  const modalPrevious = document.getElementById('aiModalPrevious');
  const modalNext = document.getElementById('aiModalNext');
  let generatedImages = [];
  let modalIndex = 0;
  let modalReturnFocus = null;
  const colorText = document.getElementById('nailColorText');
  const colorHex = document.getElementById('nailColorHex');
  const colorSwatch = document.getElementById('nailColorSwatch');
  const honeycombPicker = document.getElementById('nailHoneycombPicker');
  const colorHover = document.getElementById('nailColorHover');
  const colorHoverSwatch = document.getElementById('nailColorHoverSwatch');
  const colorHoverName = document.getElementById('nailColorHoverName');
  const colorHoverHex = document.getElementById('nailColorHoverHex');

  const setPickedColor = value => {
    const normalized = value.toUpperCase();
    if (colorHex) colorHex.value = normalized;
    if (colorText) colorText.value = normalized;
    colorSwatch?.style.setProperty('--picked-color', normalized);
  };

  colorText?.addEventListener('input', () => {
    const value = colorText.value.trim();
    if (/^#[0-9a-f]{6}$/i.test(value)) {
      setPickedColor(value);
    }
  });

  honeycombPicker?.addEventListener('click', event => {
    const cell = event.target.closest('[data-color]');
    if (!cell) return;
    honeycombPicker.querySelectorAll('[data-color]').forEach(item => {
      item.classList.toggle('selected', item === cell);
    });
    setPickedColor(cell.dataset.color);
  });

  const showColorHover = cell => {
    if (!cell || !colorHover || !honeycombPicker) return;
    const pickerBox = honeycombPicker.getBoundingClientRect();
    const cellBox = cell.getBoundingClientRect();
    const color = cell.dataset.color || '#FFFFFF';
    colorHoverSwatch?.style.setProperty('--hover-color', color);
    if (colorHoverName) colorHoverName.textContent = cell.dataset.colorName || 'Custom color';
    if (colorHoverHex) colorHoverHex.textContent = color.toUpperCase();
    colorHover.hidden = false;
    const left = cellBox.left - pickerBox.left + (cellBox.width / 2);
    const top = cellBox.top - pickerBox.top - 8;
    colorHover.style.left = `${Math.max(72, Math.min(pickerBox.width - 72, left))}px`;
    colorHover.style.top = `${Math.max(54, top)}px`;
    colorHover.style.transform = 'translate(-50%, -100%)';
  };

  const hideColorHover = () => {
    if (colorHover) colorHover.hidden = true;
  };

  honeycombPicker?.addEventListener('pointerover', event => {
    showColorHover(event.target.closest('[data-color]'));
  });
  honeycombPicker?.addEventListener('pointerleave', hideColorHover);
  honeycombPicker?.addEventListener('focusin', event => {
    showColorHover(event.target.closest('[data-color]'));
  });
  honeycombPicker?.addEventListener('focusout', hideColorHover);

  const showModalDesign = index => {
    if (!generatedImages.length || !modalImage) return;
    modalIndex = (index + generatedImages.length) % generatedImages.length;
    const design = generatedImages[modalIndex];
    modalImage.src = design.url;
    modalImage.alt = `Enlarged generated nail design ${design.number}`;
    modalTitle.textContent = `Design ${design.number}`;
    modalDownload.href = design.url;
    modalDownload.setAttribute('download', `nbw-nail-design-${design.number}.png`);
  };

  const openModal = (index, trigger) => {
    if (!modal) return;
    modalReturnFocus = trigger;
    showModalDesign(index);
    modal.hidden = false;
    document.body.classList.add('phase2-modal-open');
    modalClose?.focus();
  };

  const closeModal = () => {
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.body.classList.remove('phase2-modal-open');
    modalImage.src = '';
    modalReturnFocus?.focus();
  };

  modalClose?.addEventListener('click', closeModal);
  modalBackdrop?.addEventListener('click', closeModal);
  modalPrevious?.addEventListener('click', () => showModalDesign(modalIndex - 1));
  modalNext?.addEventListener('click', () => showModalDesign(modalIndex + 1));
  document.addEventListener('keydown', event => {
    if (!modal || modal.hidden) return;
    if (event.key === 'Escape') closeModal();
    if (event.key === 'ArrowLeft') showModalDesign(modalIndex - 1);
    if (event.key === 'ArrowRight') showModalDesign(modalIndex + 1);
  });

  const showMessage = (text, type = 'error') => {
    if (!message) return;
    message.hidden = false;
    message.className = `phase2-message ${type}`;
    message.textContent = text;
  };

  async function checkHealth() {
    if (!health) return;
    try {
      const response = await fetch(cfg.healthEndpoint, { cache: 'no-store' });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error();
      const ready = data.ready !== false && data.configured !== false;
      health.className = `phase2-status-text ${ready ? 'online' : 'offline'}`;
      health.textContent = ready
        ? `Online · ${data.provider}`
        : `${data.provider} needs configuration`;
    } catch {
      health.className = 'phase2-status-text offline';
      health.textContent = 'Python service offline';
    }
  }

  async function selectDesign(design, card, selectButton) {
    selectButton.disabled = true;
    selectButton.textContent = 'Saving…';
    try {
      const response = await fetch(cfg.selectEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          csrf_token: cfg.csrfToken,
          design_id: design.id,
        }),
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message);
      document.querySelectorAll('.phase2-result-card').forEach(
        item => item.classList.remove('selected')
      );
      document.querySelectorAll('.phase2-select').forEach(item => {
        item.disabled = false;
        item.textContent = 'Select this design';
      });
      card.classList.add('selected');
      selectButton.disabled = true;
      selectButton.textContent = 'Selected ✓';
      showMessage('Your design was saved. You can now continue to booking.', 'success');
      let bookingLink = card.querySelector('.phase2-book-design');
      if (!bookingLink) {
        bookingLink = document.createElement('a');
        bookingLink.className = 'customer-ui-button primary phase2-book-design';
        bookingLink.textContent = 'Book this design';
        card.append(bookingLink);
      }
      bookingLink.href = data.booking_url ||
        `book-ai-design.php?design_id=${design.id}`;
    } catch (error) {
      selectButton.disabled = false;
      selectButton.textContent = 'Select this design';
      showMessage(error.message || 'The design could not be saved.');
    }
  }

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    message.hidden = true;
    button.disabled = true;
    button.textContent = 'Creating concepts…';
    results.hidden = true;
    grid.innerHTML = '';

    const data = new FormData(form);
    const payload = {
      csrf_token: cfg.csrfToken,
      color: String(data.get('color') || '').trim(),
      nail_shape: data.get('nail_shape'),
      nail_length: data.get('nail_length'),
      style: data.get('style'),
      occasion: data.get('occasion'),
      instructions: String(data.get('instructions') || '').trim(),
      add_ons: data.getAll('add_ons[]'),
    };

    try {
      const response = await fetch(cfg.generateEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message);

      generatedImages = result.images;
      result.images.forEach((design, index) => {
        const card = document.createElement('article');
        card.className = 'phase2-result-card';
        const image = document.createElement('img');
        image.src = design.url;
        image.alt = `Generated nail design ${design.number}`;
        const previewButton = document.createElement('button');
        previewButton.type = 'button';
        previewButton.className = 'phase2-preview-button';
        previewButton.setAttribute(
          'aria-label',
          `Enlarge generated nail design ${design.number}`
        );
        previewButton.addEventListener('click', () =>
          openModal(index, previewButton)
        );
        previewButton.append(image);
        const title = document.createElement('h3');
        title.textContent = `Design ${design.number}`;
        const selectButton = document.createElement('button');
        selectButton.type = 'button';
        selectButton.className = 'customer-ui-button secondary phase2-select';
        selectButton.textContent = 'Select this design';
        selectButton.addEventListener('click', () =>
          selectDesign(design, card, selectButton)
        );
        card.append(previewButton, title, selectButton);
        grid.append(card);
      });
      results.hidden = false;
      results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      showMessage(error.message || 'The AI request failed.');
    } finally {
      button.disabled = false;
      button.textContent = 'Generate 3 designs';
    }
  });

  checkHealth();

  const prefill = cfg.prefill || null;
  if (prefill && form) {
    const assign = (name, value) => {
      const field = form.elements.namedItem(name);
      if (field && value) field.value = value;
    };
    assign('color', prefill.color);
    assign('nail_length', prefill.nail_length);
    assign('style', prefill.style);
    assign('occasion', prefill.occasion);
    assign('instructions', prefill.instructions);
    const shape = [...form.querySelectorAll('input[name="nail_shape"]')]
      .find(input => input.value === prefill.nail_shape);
    if (shape) shape.checked = true;
    (prefill.add_ons || []).forEach(value => {
      const addon = [...form.querySelectorAll('input[name="add_ons[]"]')]
        .find(input => input.value === value);
      if (addon) addon.checked = true;
    });
    if (/^#[0-9a-f]{6}$/i.test(prefill.color || '')) {
      setPickedColor(prefill.color);
    }
  }
})();
