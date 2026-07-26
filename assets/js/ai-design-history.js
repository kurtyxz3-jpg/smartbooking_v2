(() => {
  const cfg = window.SMARTBOOKING_AI_HISTORY || {};
  const modal = document.getElementById('aiHistoryModal');
  const modalImage = modal?.querySelector('img');
  const closeModal = () => {
    if (modal) modal.hidden = true;
    if (modalImage) modalImage.src = '';
  };

  document.querySelectorAll('.ai-history-preview').forEach(button => {
    button.addEventListener('click', () => {
      if (!modal || !modalImage) return;
      modalImage.src = button.dataset.image;
      modal.hidden = false;
    });
  });
  modal?.querySelector('.ai-history-backdrop')?.addEventListener('click', closeModal);
  modal?.querySelector('.ai-history-modal-close')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
  });

  document.querySelectorAll('.ai-history-delete').forEach(button => {
    button.addEventListener('click', async () => {
      const card = button.closest('[data-design-id]');
      if (!card || !confirm('Remove this design from your history?')) return;
      button.disabled = true;
      try {
        const response = await fetch(cfg.deleteEndpoint, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            csrf_token: cfg.csrfToken,
            design_id: Number(card.dataset.designId)
          })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message);
        card.remove();
      } catch (error) {
        alert(error.message || 'The design could not be removed.');
        button.disabled = false;
      }
    });
  });
})();

