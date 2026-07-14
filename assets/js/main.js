
document.addEventListener('DOMContentLoaded', () => {
  const menuButtons = document.querySelectorAll('[data-demo-alert]');
  menuButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      alert('This is a UI prototype. Backend functionality will be added after planning is finalized.');
    });
  });
});
