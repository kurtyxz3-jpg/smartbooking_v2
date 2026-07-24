(() => {
  const root = document.documentElement;
  const widget = document.getElementById('nbwPreferences');
  const trigger = document.getElementById('nbwPreferencesTrigger');
  const panel = document.getElementById('nbwPreferencesPanel');
  const close = document.getElementById('nbwPreferencesClose');
  const status = document.getElementById('nbwPreferencesStatus');

  if (!widget || !trigger || !panel) return;

  let language = widget.dataset.currentLanguage || 'english';
  let theme = widget.dataset.currentTheme || 'default';
  let saving = false;

  function openPanel() {
    panel.classList.add('open');
    panel.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
  }

  function closePanel() {
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
  }

  function reflectButtons() {
    document.querySelectorAll('[data-language-choice]').forEach((button) => {
      const active = button.dataset.languageChoice === language;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });

    document.querySelectorAll('[data-theme-choice]').forEach((button) => {
      const active = button.dataset.themeChoice === theme;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
  }

  async function savePreferences(reloadAfterSave) {
    if (saving) return;
    saving = true;

    if (status) status.textContent = 'Saving preferences…';

    try {
      const response = await fetch('/smartbooking_v2/api/save-preferences.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({ language, theme }),
      });

      const result = await response.json();

      if (!response.ok || !result.ok) {
        throw new Error(result.message || 'Unable to save preferences.');
      }

      localStorage.setItem('nbw_language', language);
      localStorage.setItem('nbw_theme', theme);

      if (status) status.textContent = 'Preferences saved.';

      if (reloadAfterSave) {
        window.setTimeout(() => window.location.reload(), 180);
      }
    } catch (error) {
      if (status) {
        status.textContent =
          error instanceof Error
            ? error.message
            : 'Unable to save preferences.';
      }
    } finally {
      saving = false;
    }
  }

  trigger.addEventListener('click', () => {
    panel.classList.contains('open') ? closePanel() : openPanel();
  });

  close?.addEventListener('click', closePanel);

  document.addEventListener('click', (event) => {
    if (!widget.contains(event.target)) {
      closePanel();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closePanel();
      trigger.focus();
    }
  });

  document.querySelectorAll('[data-theme-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      theme = button.dataset.themeChoice || 'default';
      root.dataset.theme = theme;
      reflectButtons();
      savePreferences(false);
    });
  });

  document.querySelectorAll('[data-language-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      language = button.dataset.languageChoice || 'english';
      reflectButtons();
      savePreferences(true);
    });
  });

  reflectButtons();
})();
