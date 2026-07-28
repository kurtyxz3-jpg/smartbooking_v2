(() => {
  const modal = document.getElementById('promotionModal');
  if (!modal) return;

  const form = modal.querySelector('form');
  const originalPrice = modal.querySelector('[data-promotion-original-price]');
  const discount = modal.querySelector('[data-promotion-discount]');
  const offerPrice = modal.querySelector('[data-promotion-price]');
  const uploadArea = modal.querySelector('[data-promotion-upload]');
  const fileInput = modal.querySelector('[data-promotion-image-input]');
  const preview = modal.querySelector('[data-promotion-image-preview]');
  const previewImage = preview?.querySelector('img');
  const previewEmpty = modal.querySelector('[data-promotion-image-empty]');
  const chooseImage = modal.querySelector('[data-promotion-choose-image]');
  const clearImage = modal.querySelector('[data-promotion-clear-image]');
  const storedImagePath = modal.querySelector('[data-field="image_path"]');
  const removeImage = modal.querySelector('[data-promotion-remove-image]');
  let previewObjectUrl = '';

  function calculateOfferPrice() {
    if (!originalPrice || !discount || !offerPrice) return;

    const original = Number(originalPrice.value);
    const percentage = Math.min(100, Math.max(0, Number(discount.value) || 0));
    offerPrice.value = originalPrice.value === ''
      ? ''
      : (original * (1 - percentage / 100)).toFixed(2);
  }

  function showImage(source = '') {
    if (!previewImage || !previewEmpty || !clearImage) return;

    if (previewObjectUrl) {
      URL.revokeObjectURL(previewObjectUrl);
      previewObjectUrl = '';
    }

    previewImage.hidden = source === '';
    previewEmpty.hidden = source !== '';
    clearImage.hidden = source === '';
    previewImage.src = source;
  }

  function useSelectedFile(file) {
    if (!file || !fileInput) return;
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowed.includes(file.type)) {
      window.alert('Choose a JPG, PNG, or WebP photo.');
      fileInput.value = '';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      window.alert('Promotion photos must be 5 MB or smaller.');
      fileInput.value = '';
      return;
    }

    if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
    previewObjectUrl = URL.createObjectURL(file);
    if (previewImage) {
      previewImage.hidden = false;
      previewImage.src = previewObjectUrl;
    }
    if (previewEmpty) previewEmpty.hidden = true;
    if (clearImage) clearImage.hidden = false;
    if (removeImage) removeImage.value = '';
  }

  originalPrice?.addEventListener('input', calculateOfferPrice);
  discount?.addEventListener('input', calculateOfferPrice);
  chooseImage?.addEventListener('click', () => fileInput?.click());
  preview?.addEventListener('click', () => fileInput?.click());
  fileInput?.addEventListener('change', () => useSelectedFile(fileInput.files?.[0]));

  ['dragenter', 'dragover'].forEach((eventName) => {
    uploadArea?.addEventListener(eventName, (event) => {
      event.preventDefault();
      uploadArea.classList.add('is-dragging');
    });
  });

  ['dragleave', 'drop'].forEach((eventName) => {
    uploadArea?.addEventListener(eventName, (event) => {
      event.preventDefault();
      uploadArea.classList.remove('is-dragging');
    });
  });

  uploadArea?.addEventListener('drop', (event) => {
    const file = event.dataTransfer?.files?.[0];
    if (!file || !fileInput) return;

    const transfer = new DataTransfer();
    transfer.items.add(file);
    fileInput.files = transfer.files;
    useSelectedFile(file);
  });

  clearImage?.addEventListener('click', () => {
    if (fileInput) fileInput.value = '';
    if (storedImagePath) storedImagePath.value = '';
    if (removeImage) removeImage.value = '1';
    showImage('');
  });

  modal.querySelectorAll('[data-premium-modal-open="promotionModal"]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!form) return;
      const payload = button.dataset.payload ? JSON.parse(button.dataset.payload) : {};

      form.reset();
      modal.querySelectorAll('[data-field]').forEach((field) => {
        const key = field.dataset.field;
        if (!key) return;
        if (field.type === 'checkbox') {
          field.checked = Number(payload[key] || 0) === 1;
        } else {
          field.value = payload[key] ?? '';
        }
      });

      if (fileInput) fileInput.value = '';
      if (removeImage) removeImage.value = '';
      showImage(String(payload.image_path || ''));
      calculateOfferPrice();
    });
  });
})();
