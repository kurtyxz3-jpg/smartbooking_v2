(() => {
  'use strict';

  const root = document.documentElement;
  const widget = document.getElementById('nbwPreferences');
  const trigger = document.getElementById('nbwPreferencesTrigger');
  const panel = document.getElementById('nbwPreferencesPanel');
  const closeButton = document.getElementById('nbwPreferencesClose');
  const status = document.getElementById('nbwPreferencesStatus');

  if (!widget || !trigger || !panel) return;

  const loadedUrl = new URL(window.location.href);
  if (loadedUrl.searchParams.has('_language_changed')) {
    loadedUrl.searchParams.delete('_language_changed');
    window.history.replaceState({}, '', loadedUrl.toString());
  }

  const allowedLanguages = new Set(['english', 'filipino', 'taglish']);
  const allowedThemes = new Set(['default', 'light', 'dark']);

  let language = allowedLanguages.has(widget.dataset.currentLanguage)
    ? widget.dataset.currentLanguage
    : 'english';
  let theme = allowedThemes.has(widget.dataset.currentTheme)
    ? widget.dataset.currentTheme
    : 'default';
  let saving = false;
  let languageReloadTimer = null;
  const positionKey = 'nbw_preferences_position';
  let suppressTriggerClick = false;

  function placeWidget(left, top) {
    const margin = 8;
    const x = Math.max(margin, Math.min(left, window.innerWidth - widget.offsetWidth - margin));
    const y = Math.max(margin, Math.min(top, window.innerHeight - widget.offsetHeight - margin));
    widget.style.left = `${x}px`;
    widget.style.top = `${y}px`;
    widget.style.right = 'auto';
    widget.style.bottom = 'auto';
    widget.classList.toggle('panel-below', y < Math.min(420, window.innerHeight / 2));
    widget.classList.toggle('panel-align-left', x + widget.offsetWidth < Math.min(370, window.innerWidth - 28) + 24);
    return { x, y };
  }

  try {
    const savedPosition = JSON.parse(localStorage.getItem(positionKey) || 'null');
    if (Number.isFinite(savedPosition?.x) && Number.isFinite(savedPosition?.y)) {
      placeWidget(savedPosition.x, savedPosition.y);
    }
  } catch (_) {
    // The default corner remains available if browser storage is unavailable.
  }

  let drag = null;
  trigger.addEventListener('pointerdown', (event) => {
    if (event.button !== 0) return;
    const rect = widget.getBoundingClientRect();
    drag = { pointerId: event.pointerId, startX: event.clientX, startY: event.clientY,
      left: rect.left, top: rect.top, moved: false };
    trigger.setPointerCapture(event.pointerId);
  });

  trigger.addEventListener('pointermove', (event) => {
    if (!drag || event.pointerId !== drag.pointerId) return;
    const dx = event.clientX - drag.startX;
    const dy = event.clientY - drag.startY;
    if (!drag.moved && Math.hypot(dx, dy) < 6) return;
    drag.moved = true;
    trigger.classList.add('is-dragging');
    closePanel();
    placeWidget(drag.left + dx, drag.top + dy);
  });

  function finishDrag(event) {
    if (!drag || event.pointerId !== drag.pointerId) return;
    if (drag.moved) {
      suppressTriggerClick = true;
      const rect = widget.getBoundingClientRect();
      try { localStorage.setItem(positionKey, JSON.stringify({ x: rect.left, y: rect.top })); } catch (_) {}
      window.setTimeout(() => { suppressTriggerClick = false; }, 0);
    }
    trigger.classList.remove('is-dragging');
    drag = null;
  }

  trigger.addEventListener('pointerup', finishDrag);
  trigger.addEventListener('pointercancel', finishDrag);
  window.addEventListener('resize', () => {
    if (widget.style.left) {
      const rect = widget.getBoundingClientRect();
      placeWidget(rect.left, rect.top);
    }
  });

  const labels = {
    english: {
      saving: 'Saving preferences…',
      changing: 'Changing language…',
      saved: 'Preferences saved.',
      error: 'Unable to save the account preference.',
    },
    filipino: {
      saving: 'Sine-save ang preferences…',
      changing: 'Pinapalitan ang wika…',
      saved: 'Naka-save na ang preferences.',
      error: 'Hindi ma-save ang account preference.',
    },
    taglish: {
      saving: 'Saving preferences…',
      changing: 'Changing language…',
      saved: 'Saved na ang preferences.',
      error: 'Hindi ma-save ang account preference.',
    },
  };

  function currentLabels() {
    return labels[language] || labels.english;
  }

  function setStatus(message) {
    if (status) status.textContent = message;
  }

  function setCookie(name, value) {
    const maxAge = 60 * 60 * 24 * 365;
    document.cookie =
      `${name}=${encodeURIComponent(value)}; ` +
      `path=/smartbooking_v2/; max-age=${maxAge}; SameSite=Lax`;
  }

  function persistLocally() {
    localStorage.setItem('nbw_language', language);
    localStorage.setItem('nbw_theme', theme);
    setCookie('nbw_language', language);
    setCookie('nbw_theme', theme);
  }

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

  async function sendPreferenceRequest() {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 2500);

    try {
      const response = await fetch(
        '/smartbooking_v2/api/save-preferences.php',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          cache: 'no-store',
          signal: controller.signal,
          body: JSON.stringify({ language, theme }),
        }
      );

      let result = {};
      try {
        result = await response.json();
      } catch (_) {
        result = {};
      }

      if (!response.ok || result.ok === false) {
        throw new Error(result.message || currentLabels().error);
      }

      return result;
    } finally {
      window.clearTimeout(timeout);
    }
  }

  function reloadForLanguageChange() {
    if (languageReloadTimer) {
      window.clearTimeout(languageReloadTimer);
    }

    /*
     * PHP performs the actual interface translation. A cache-busting query
     * guarantees a fresh request, while replace() avoids adding a duplicate
     * page to browser history.
     */
    languageReloadTimer = window.setTimeout(() => {
      const url = new URL(window.location.href);
      url.searchParams.set('_language_changed', String(Date.now()));
      window.location.replace(url.toString());
    }, 120);
  }

  async function saveTheme() {
    if (saving) return;
    saving = true;

    persistLocally();
    root.dataset.theme = theme;
    reflectButtons();
    setStatus(currentLabels().saving);

    try {
      await sendPreferenceRequest();
      setStatus(currentLabels().saved);
    } catch (error) {
      /*
       * Theme remains saved locally even if the optional account database
       * update fails. This keeps accessibility controls responsive.
       */
      setStatus(
        error instanceof Error ? error.message : currentLabels().error
      );
    } finally {
      saving = false;
    }
  }

  async function saveLanguageAndReload() {
    if (saving) return;
    saving = true;

    /*
     * Save browser state before contacting PHP. This guarantees that the next
     * request already contains the chosen language cookie.
     */
    persistLocally();
    reflectButtons();
    setStatus(currentLabels().changing);

    /*
     * Always schedule the automatic reload. The database request may be slow
     * or unavailable, but the cookie is enough for PHP to render the selected
     * language on this device.
     */
    const fallbackReload = window.setTimeout(reloadForLanguageChange, 900);

    try {
      await sendPreferenceRequest();
    } catch (_) {
      // The browser preference is already saved; continue with the reload.
    } finally {
      window.clearTimeout(fallbackReload);
      reloadForLanguageChange();
    }
  }

  trigger.addEventListener('click', () => {
    if (suppressTriggerClick) return;
    panel.classList.contains('open') ? closePanel() : openPanel();
  });

  closeButton?.addEventListener('click', closePanel);

  document.addEventListener('click', (event) => {
    if (!widget.contains(event.target)) closePanel();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closePanel();
      trigger.focus();
    }
  });

  document.querySelectorAll('[data-theme-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      const choice = button.dataset.themeChoice || 'default';
      if (!allowedThemes.has(choice) || choice === theme) return;

      theme = choice;
      saveTheme();
    });
  });

  document.querySelectorAll('[data-language-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      const choice = button.dataset.languageChoice || 'english';
      if (!allowedLanguages.has(choice) || choice === language) return;

      language = choice;
      saveLanguageAndReload();
    });
  });

  reflectButtons();
})();
