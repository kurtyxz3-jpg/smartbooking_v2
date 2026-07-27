(() => {
  const modal = document.getElementById('paymentModal');
  const form = modal?.querySelector('form');
  const status = form?.querySelector('[name="status"]');

  if (!form || !status) return;

  form.addEventListener('submit', event => {
    let note = form.querySelector('[name="review_note"]');

    if (!note) {
      note = document.createElement('input');
      note.type = 'hidden';
      note.name = 'review_note';
      form.appendChild(note);
    }

    if (status.value === 'rejected') {
      const reason = window.prompt(
        'Why was this payment rejected? This note will be shown to the customer.',
        note.value || ''
      );

      if (reason === null || reason.trim() === '') {
        event.preventDefault();
        return;
      }

      note.value = reason.trim();
    }
  });
})();
