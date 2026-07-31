(() => {
  const modal = document.getElementById('paymentModal');
  if (!modal) return;
  const money = value => new Intl.NumberFormat('en-PH', {style:'currency',currency:'PHP'}).format(Number(value || 0));
  document.querySelectorAll('[data-premium-modal-open="paymentModal"]').forEach(button => button.addEventListener('click', () => {
    let payment = {}; try { payment = JSON.parse(button.dataset.payload || '{}'); } catch (_) {}
    modal.querySelectorAll('[data-receipt]').forEach(el => el.textContent = payment[el.dataset.receipt] || '—');
    modal.querySelectorAll('[data-money]').forEach(el => el.textContent = money(payment[el.dataset.money]));
    const image = modal.querySelector('[data-receipt-proof-image]');
    const empty = modal.querySelector('[data-receipt-proof-empty]');
    if (image && payment.proof_path) {
      image.src = '../' + String(payment.proof_path).replace(/^\/+/, '');
      image.hidden = false; if (empty) empty.hidden = true;
    } else {
      if (image) { image.hidden = true; image.removeAttribute('src'); }
      if (empty) empty.hidden = false;
    }
  }));
  modal.querySelector('[data-print-receipt]')?.addEventListener('click', () => {
    const receipt = document.getElementById('printableReceipt');
    if (!receipt) return;
    const win = window.open('', '_blank', 'width=820,height=900');
    if (!win) return;
    win.document.write(`<!doctype html><html><head><title>SmartBooking Receipt</title><style>@page{size:A4;margin:12mm}*{box-sizing:border-box}html,body{margin:0;font:13px Arial,sans-serif;color:#21150f}.sb-receipt{max-width:720px;margin:0 auto;padding:20px;border:1px solid #d8c5ac}.sb-receipt-brand,.sb-receipt dl div,.sb-receipt-totals p{display:flex;justify-content:space-between;gap:18px}.sb-receipt-brand{padding-bottom:12px;border-bottom:2px solid #c89b3c}.sb-receipt-brand strong{font:700 24px Georgia,serif}.sb-receipt-number{padding:10px 0;color:#6e625a}.sb-receipt dl{margin:0}.sb-receipt dl div,.sb-receipt-totals p{margin:0;padding:5px 0}.sb-receipt dd{margin:0;text-align:right;font-weight:700}.sb-receipt-totals{margin-top:9px;padding-top:8px;border-top:1px dashed #cfc2b3}.sb-receipt-totals .total{padding-top:9px;border-top:2px solid #21150f;font-size:15px}.sb-receipt-proof{margin-top:12px;padding-top:10px;border-top:1px solid #ddd}.sb-receipt-proof p{font-weight:700;margin:0 0 8px}.sb-receipt-proof img{display:block;max-width:100%;max-height:360px;margin:auto;object-fit:contain}.sb-receipt-proof [hidden]{display:none!important}</style></head><body>${receipt.outerHTML}</body></html>`);
    win.document.close(); win.focus();
    const finish = () => { win.print(); win.close(); };
    const proof = win.document.querySelector('[data-receipt-proof-image]:not([hidden])');
    if (proof && !proof.complete) proof.addEventListener('load', finish, {once:true}); else setTimeout(finish, 150);
  });
  modal.querySelector('form')?.addEventListener('submit', event => {
    const status = modal.querySelector('[name="status"]'); const note = modal.querySelector('[name="review_note"]');
    if (status?.value === 'rejected' && !note?.value.trim()) { event.preventDefault(); note?.setCustomValidity('Add a reason for rejecting this payment.'); note?.reportValidity(); }
    else note?.setCustomValidity('');
  });
})();
