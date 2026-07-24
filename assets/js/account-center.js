(() => {
  const photoInput = document.getElementById('accountPhotoInput');
  const photoPreview = document.getElementById('accountPhotoPreview');
  const initials = document.getElementById('accountPhotoInitials');

  photoInput?.addEventListener('change', () => {
    const file = photoInput.files?.[0];
    if (!file || !photoPreview) return;

    const reader = new FileReader();
    reader.onload = () => {
      photoPreview.src = String(reader.result);
      photoPreview.hidden = false;
      if (initials) initials.hidden = true;
    };
    reader.readAsDataURL(file);
  });

  const selectedTheme = document.querySelector(
    'input[name="theme_mode"]:checked'
  );

  document.querySelectorAll('input[name="theme_mode"]').forEach((input) => {
    input.addEventListener('change', () => {
      document.documentElement.dataset.theme = input.value;
      localStorage.setItem('smartbooking_theme', input.value);
    });
  });

  if (selectedTheme) {
    selectedTheme.closest('.account-center-choice')?.classList.add('selected');
  }
})();
