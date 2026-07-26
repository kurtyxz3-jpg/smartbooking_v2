(() => {
  const cfg = window.SMARTBOOKING_BEAUTY_AI || {};
  const form = document.getElementById('beautyStudioForm');
  const photo = document.getElementById('beautyPhoto');
  const preview = document.getElementById('beautyPhotoPreview');
  const useDemo = document.getElementById('beautyUseDemo');
  const picker = document.getElementById('beautyColorPicker');
  const colorText = document.getElementById('beautyColorText');
  const health = document.getElementById('beautyHealth');
  const message = document.getElementById('beautyMessage');
  const button = document.getElementById('beautyGenerateButton');
  const results = document.getElementById('beautyResults');
  const grid = document.getElementById('beautyResultGrid');
  let processedImage = '';

  const showMessage = (text, type = 'error') => {
    if (!message) return;
    message.hidden = false;
    message.className = `phase2-message ${type}`;
    message.textContent = text;
  };

  picker?.addEventListener('input', () => {
    colorText.value = picker.value.toUpperCase();
  });
  colorText?.addEventListener('input', () => {
    if (/^#[0-9a-f]{6}$/i.test(colorText.value.trim())) {
      picker.value = colorText.value.trim();
    }
  });

  const preparePhoto = file => new Promise((resolve, reject) => {
    if (!file) return resolve('');
    if (file.size > 10 * 1024 * 1024) {
      return reject(new Error('Choose a photo smaller than 10 MB.'));
    }
    const reader = new FileReader();
    reader.onerror = () => reject(new Error('The photo could not be read.'));
    reader.onload = () => {
      const image = new Image();
      image.onerror = () => reject(new Error('Choose a valid JPG, PNG, or WebP photo.'));
      image.onload = () => {
        const side = Math.min(image.width, image.height);
        const sourceX = Math.round((image.width - side) / 2);
        const sourceY = Math.round((image.height - side) / 2);
        const outputSize = Math.min(1024, side);
        const canvas = document.createElement('canvas');
        canvas.width = outputSize;
        canvas.height = outputSize;
        canvas.getContext('2d').drawImage(
          image, sourceX, sourceY, side, side, 0, 0, outputSize, outputSize
        );
        resolve(canvas.toDataURL('image/jpeg', .86));
      };
      image.src = reader.result;
    };
    reader.readAsDataURL(file);
  });

  photo?.addEventListener('change', async () => {
    try {
      processedImage = await preparePhoto(photo.files[0]);
      if (processedImage) preview.src = processedImage;
    } catch (error) {
      showMessage(error.message);
      photo.value = '';
    }
  });
  useDemo?.addEventListener('click', () => {
    processedImage = '';
    photo.value = '';
    preview.src = cfg.demoImage;
  });

  async function checkHealth() {
    if (!health) return;
    try {
      const response = await fetch(cfg.healthEndpoint, {cache: 'no-store'});
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error();
      health.className = 'phase2-health online';
      health.textContent = `Online · ${data.provider}`;
    } catch {
      health.className = 'phase2-health offline';
      health.textContent = 'Python service offline';
    }
  }

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    message.hidden = true;
    button.disabled = true;
    button.textContent = 'Creating previews…';
    const data = new FormData(form);
    const payload = {
      csrf_token: cfg.csrfToken,
      area: data.get('area'),
      color: data.get('color'),
      finish: data.get('finish'),
      service_style: data.get('service_style'),
      brow_shape: data.get('brow_shape'),
      thickness: data.get('thickness'),
      instructions: data.get('instructions'),
      consent: data.get('consent') ? true : false,
      image_data: processedImage,
      count: 3
    };
    try {
      const response = await fetch(cfg.generateEndpoint, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message);
      grid.innerHTML = '';
      result.images.forEach(item => {
        const card = document.createElement('article');
        card.className = 'beauty-result-card';
        card.innerHTML = `<a href="${item.url}" target="_blank"><img src="${item.url}" alt="Beauty preview ${item.number}"></a><h3>Preview ${item.number}</h3>`;
        const select = document.createElement('button');
        select.type = 'button';
        select.className = 'customer-ui-button secondary';
        select.textContent = 'Select this preview';
        select.addEventListener('click', async () => {
          const response = await fetch(cfg.selectEndpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({csrf_token: cfg.csrfToken, result_id: item.id})
          });
          const saved = await response.json();
          if (!response.ok || !saved.success) return showMessage(saved.message);
          document.querySelectorAll('.beauty-result-card').forEach(
            element => element.classList.remove('selected')
          );
          card.classList.add('selected');
          showMessage(saved.message, 'success');
        });
        card.append(select);
        grid.append(card);
      });
      results.hidden = false;
      results.scrollIntoView({behavior: 'smooth'});
    } catch (error) {
      showMessage(error.message || 'The preview could not be generated.');
    } finally {
      button.disabled = false;
      button.textContent = 'Generate 3 previews';
    }
  });
  checkHealth();
})();
