(() => {
  const config = window.SMARTBOOKING_AI_CONFIG || {};
  const form = document.getElementById('customerAiForm');
  const message = document.getElementById('customerAiMessage');
  const generateButton = document.getElementById(
    'customerAiGenerateButton'
  );
  const buttonLabel = generateButton?.querySelector(
    '.customer-ai-button-label'
  );
  const buttonLoading = generateButton?.querySelector(
    '.customer-ai-button-loading'
  );
  const results = document.getElementById('customerAiResults');
  const resultGrid = document.getElementById('customerAiResultGrid');
  const resultMessage = document.getElementById(
    'customerAiResultMessage'
  );
  const generateAgain = document.getElementById(
    'customerAiGenerateAgain'
  );
  const selectedPanel = document.getElementById(
    'customerAiSelectedPanel'
  );
  const instructions = document.getElementById('instructions');
  const characterCount = document.getElementById(
    'customerAiCharacterCount'
  );
  const serverChip = document.getElementById(
    'customerAiServerChip'
  );

  let currentGenerationId = null;

  function showMessage(text, type = 'error') {
    if (!message) return;

    message.hidden = false;
    message.className = `customer-ai-message ${type}`;
    message.textContent = text;
  }

  function clearMessage() {
    if (!message) return;
    message.hidden = true;
    message.textContent = '';
  }

  function setLoading(isLoading) {
    if (!generateButton) return;

    generateButton.disabled = isLoading;
    if (buttonLabel) buttonLabel.hidden = isLoading;
    if (buttonLoading) buttonLoading.hidden = !isLoading;
  }

  function collectPayload() {
    const addOns = [
      ...form.querySelectorAll(
        'input[name="add_ons[]"]:checked'
      ),
    ].map((input) => input.value);

    return {
      color: form.elements.color.value.trim(),
      style: form.elements.style.value,
      nail_shape: form.elements.nail_shape.value,
      nail_length: form.elements.nail_length.value,
      occasion: form.elements.occasion.value,
      add_ons: addOns,
      instructions: form.elements.instructions.value.trim(),
    };
  }

  function createResultCard(image, generationId) {
    const article = document.createElement('article');
    article.className = 'customer-ai-result-card';

    const figure = document.createElement('figure');
    const img = document.createElement('img');
    img.src = image.url;
    img.alt = `Generated nail concept ${image.number}`;
    img.loading = 'lazy';

    const badge = document.createElement('span');
    badge.textContent = `Concept ${image.number}`;

    figure.append(img, badge);

    const body = document.createElement('div');
    body.className = 'customer-ai-result-card-body';

    const heading = document.createElement('h3');
    heading.textContent = `Design direction ${image.number}`;

    const copy = document.createElement('p');
    copy.textContent =
      'Generated from your selected colors, style, shape, and add-ons.';

    const button = document.createElement('button');
    button.className = 'customer-ui-button secondary';
    button.type = 'button';
    button.textContent = 'Select this design';

    button.addEventListener('click', async () => {
      button.disabled = true;
      button.textContent = 'Saving selection';

      try {
        const response = await fetch(config.selectEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            generation_id: generationId,
            selected_number: image.number,
            selected_url: image.url,
          }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(
            data.message || 'The design could not be saved.'
          );
        }

        document
          .querySelectorAll('.customer-ai-result-card')
          .forEach((card) => card.classList.remove('selected'));

        article.classList.add('selected');

        document
          .querySelectorAll('.customer-ai-result-card button')
          .forEach((cardButton) => {
            cardButton.disabled = false;
            cardButton.textContent = 'Select this design';
          });

        button.textContent = 'Selected ✓';
        button.disabled = true;

        if (selectedPanel) {
          selectedPanel.hidden = false;
          selectedPanel.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
          });
        }
      } catch (error) {
        button.disabled = false;
        button.textContent = 'Select this design';
        showMessage(error.message);
      }
    });

    body.append(heading, copy, button);
    article.append(figure, body);

    return article;
  }

  async function checkServer() {
    if (!serverChip || !config.healthEndpoint) return;

    try {
      const response = await fetch(config.healthEndpoint, {
        cache: 'no-store',
      });
      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error();
      }

      serverChip.classList.add('online');
      serverChip.innerHTML =
        '<span></span> AI server online · '
        + String(data.provider || 'demo');
    } catch {
      serverChip.classList.remove('online');
      serverChip.classList.add('offline');
      serverChip.innerHTML =
        '<span></span> AI server offline';
    }
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearMessage();

    const payload = collectPayload();

    if (!payload.color) {
      showMessage('Enter your preferred colors.');
      form.elements.color.focus();
      return;
    }

    setLoading(true);
    if (results) results.hidden = true;
    if (selectedPanel) selectedPanel.hidden = true;
    if (resultGrid) resultGrid.innerHTML = '';

    try {
      const response = await fetch(config.generateEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(
          data.message || 'The AI request could not be completed.'
        );
      }

      currentGenerationId = data.generation_id || null;

      data.images.forEach((image) => {
        resultGrid.append(
          createResultCard(image, currentGenerationId)
        );
      });

      if (resultMessage) {
        resultMessage.textContent =
          data.message
          + ' Select one design to save it to your account.';
      }

      if (results) {
        results.hidden = false;
        results.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
    } catch (error) {
      showMessage(
        error.message
        || 'The Python AI server may not be running.'
      );
    } finally {
      setLoading(false);
    }
  });

  generateAgain?.addEventListener('click', () => {
    form?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
    form?.elements.color.focus();
  });

  instructions?.addEventListener('input', () => {
    if (characterCount) {
      characterCount.textContent = String(
        instructions.value.length
      );
    }
  });

  checkServer();
  window.setInterval(checkServer, 30000);
})();
