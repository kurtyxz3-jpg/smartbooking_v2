(() => {
  const modal = document.getElementById('serviceModal');
  const form = document.getElementById('serviceForm');
  const action = document.getElementById('serviceAction');
  const id = document.getElementById('serviceId');
  const name = document.getElementById('serviceName');
  const category = document.getElementById('serviceCategory');
  const slug = document.getElementById('serviceSlug');
  const description = document.getElementById('serviceDescription');
  const price = document.getElementById('servicePrice');
  const priceNote = document.getElementById('servicePriceNote');
  const duration = document.getElementById('serviceDuration');
  const imagePath = document.getElementById('serviceImagePath');
  const existingImage = document.getElementById('serviceExistingImage');
  const imageInput = document.getElementById('serviceImage');
  const imagePreview = document.getElementById('serviceImagePreview');
  const imageUpload = document.querySelector('[data-service-image-upload]');
  const imageDrop = document.querySelector('[data-service-image-drop]');
  const imageChoose = document.querySelector('[data-service-image-choose]');
  const imageClear = document.querySelector('[data-service-image-clear]');
  const featured = document.getElementById('serviceFeatured');
  const displayOrder = document.getElementById('serviceDisplayOrder');
  const status = document.getElementById('serviceStatus');
  const title = document.getElementById('serviceModalTitle');
  const descriptionText = document.getElementById(
    'serviceModalDescription'
  );
  const submit = document.getElementById('serviceSubmitButton');

  const fallbackImage =
    '../assets/images/services/temporary-placeholders/'
    + 'brow-reconstruction.webp';

  function resolveImage(path) {
    if (!path) return fallbackImage;
    if (path.startsWith('/')) return path;
    return '../' + path.replace(/^\/+/, '');
  }

  function showSelectedImage(file) {
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
      window.alert('Choose a JPG, PNG, or WebP image.');
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      window.alert('The service image must be 5 MB or smaller.');
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      imagePreview.src = String(reader.result);
      imageClear.hidden = false;
    };
    reader.readAsDataURL(file);
  }

  function openModal(mode, payload = null) {
    form?.reset();

    action.value = mode === 'edit' ? 'update' : 'create';
    id.value = '';
    existingImage.value = '';
    imagePreview.src = fallbackImage;
    imageClear.hidden = true;

    if (mode === 'edit' && payload) {
      title.textContent = 'Edit Service';
      descriptionText.textContent =
        'Update service details, image, price, and availability.';
      submit.textContent = 'Save Changes';

      id.value = payload.id ?? '';
      name.value = payload.name ?? '';
      category.value = payload.category_id ?? '';
      slug.value = payload.slug ?? '';
      description.value = payload.description ?? '';
      price.value = payload.price ?? '';
      priceNote.value = payload.price_note ?? '';
      duration.value = payload.duration ?? 60;
      imagePath.value = '';
      existingImage.value = payload.image ?? '';
      featured.checked = Number(payload.featured) === 1;
      displayOrder.value = payload.display_order ?? 0;
      status.value = payload.status ?? 'active';
      imagePreview.src = resolveImage(payload.image);
      imageClear.hidden = !payload.image;
    } else {
      title.textContent = 'Add Service';
      descriptionText.textContent =
        'Add a service that customers can view and book.';
      submit.textContent = 'Add Service';
      duration.value = 60;
      displayOrder.value = 0;
      status.value = 'active';
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    window.setTimeout(() => name.focus(), 60);
  }

  function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-service-open="create"]').forEach(
    (button) => {
      button.addEventListener('click', () => openModal('create'));
    }
  );

  document.querySelectorAll('[data-service-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      try {
        openModal(
          'edit',
          JSON.parse(button.dataset.serviceEdit || '{}')
        );
      } catch {
        window.alert('The service record could not be opened.');
      }
    });
  });

  document.querySelectorAll('[data-service-close]').forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('open')) {
      closeModal();
    }
  });

  name?.addEventListener('input', () => {
    if (action.value !== 'create' || slug.dataset.touched === 'true') {
      return;
    }

    slug.value = name.value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '');
  });

  slug?.addEventListener('input', () => {
    slug.dataset.touched = 'true';
  });

  imagePath?.addEventListener('input', () => {
    imagePreview.src = resolveImage(imagePath.value.trim());
  });

  imageInput?.addEventListener('change', () => {
    showSelectedImage(imageInput.files?.[0]);
  });

  imageChoose?.addEventListener('click', () => imageInput.click());
  imageDrop?.addEventListener('click', () => imageInput.click());

  ['dragenter', 'dragover'].forEach((eventName) => {
    imageDrop?.addEventListener(eventName, (event) => {
      event.preventDefault();
      imageUpload?.classList.add('is-dragging');
    });
  });

  ['dragleave', 'drop'].forEach((eventName) => {
    imageDrop?.addEventListener(eventName, (event) => {
      event.preventDefault();
      imageUpload?.classList.remove('is-dragging');
    });
  });

  imageDrop?.addEventListener('drop', (event) => {
    const file = event.dataTransfer?.files?.[0];
    if (!file) return;

    const transfer = new DataTransfer();
    transfer.items.add(file);
    imageInput.files = transfer.files;
    showSelectedImage(file);
  });

  imageClear?.addEventListener('click', () => {
    imageInput.value = '';
    imagePath.value = '';
    existingImage.value = '';
    imagePreview.src = fallbackImage;
    imageClear.hidden = true;
  });

  document.querySelectorAll('[data-service-remove-form]').forEach(
    (removeForm) => {
      removeForm.addEventListener('submit', (event) => {
        const bookingCount = Number(
          removeForm.dataset.bookingCount || 0
        );

        const message =
          bookingCount > 0
            ? 'This service has booking history. It will be archived and hidden instead of permanently deleted. Continue?'
            : 'Permanently remove this unused service? This action cannot be undone.';

        if (!window.confirm(message)) {
          event.preventDefault();
        }
      });
    }
  );

  /* ------------------------------------------------------------
     Instant visible-card search
     ------------------------------------------------------------
     This filters the cards currently loaded on the page while typing.
     The Apply Filters button still performs the full database search,
     including records on other pagination pages.
  */
  const liveSearchInput = document.querySelector('[data-admin-live-search]');
  const serviceCards = [...document.querySelectorAll('[data-service-card]')];
  const liveStatus = document.getElementById('adminServiceLiveStatus');
  const liveEmpty = document.getElementById('adminServiceLiveEmpty');

  function normalizeSearchText(value) {
    return String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  }

  function filterVisibleServiceCards() {
    if (!liveSearchInput || serviceCards.length === 0) return;

    const query = normalizeSearchText(liveSearchInput.value);
    let visibleCount = 0;

    serviceCards.forEach((card) => {
      const haystack = normalizeSearchText(card.dataset.searchText);
      const visible = query === '' || haystack.includes(query);

      card.hidden = !visible;

      if (visible) {
        visibleCount++;
      }
    });

    if (liveEmpty) {
      liveEmpty.hidden = visibleCount !== 0;
    }

    if (liveStatus) {
      if (query === '') {
        liveStatus.textContent =
          `${serviceCards.length} service card`
          + `${serviceCards.length === 1 ? '' : 's'} currently displayed.`;
      } else {
        liveStatus.textContent =
          `${visibleCount} matching service`
          + `${visibleCount === 1 ? '' : 's'} on this page.`;
      }
    }
  }

  liveSearchInput?.addEventListener('input', filterVisibleServiceCards);

  liveSearchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      liveSearchInput.value = '';
      filterVisibleServiceCards();
      liveSearchInput.focus();
    }
  });

  filterVisibleServiceCards();

})();
