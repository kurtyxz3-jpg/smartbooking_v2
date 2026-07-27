(() => {
  'use strict';

  const summary = document.getElementById('aiHealthSummary');
  const refreshButton = document.getElementById('refreshAiStatus');

  if (!summary || !refreshButton) {
    return;
  }

  const badge = document.getElementById('aiServiceBadge');
  const badgeText = document.getElementById('aiServiceBadgeText');
  const statusText = document.getElementById('aiHealthStatus');
  const providerText = document.getElementById('aiHealthProvider');
  const modelText = document.getElementById('aiHealthModel');
  const configuredText = document.getElementById('aiHealthConfigured');
  const message = document.getElementById('aiHealthMessage');
  const healthUrl = summary.dataset.healthUrl || 'api/ai-health.php';

  const setLoading = (loading) => {
    refreshButton.disabled = loading;
    refreshButton.textContent = loading ? 'Checking…' : 'Refresh Status';
  };

  const render = (data) => {
    const online = Boolean(data.success);

    badge.classList.toggle('is-online', online);
    badge.classList.toggle('is-offline', !online);
    badgeText.textContent = online
      ? 'Python service online'
      : 'Python service offline';

    statusText.textContent = online ? 'Healthy' : 'Offline';
    providerText.textContent = data.provider || 'Unavailable';
    modelText.textContent = data.model || 'Unavailable';
    configuredText.textContent = data.configured ? 'Yes' : 'No';

    if (online) {
      message.hidden = true;
      message.textContent = '';
    } else {
      message.hidden = false;
      message.textContent =
        data.message || 'Start the Python AI service and try again.';
    }
  };

  const refreshHealth = async () => {
    setLoading(true);

    try {
      const response = await fetch(healthUrl, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      });

      const data = await response.json();
      render(data);
    } catch (error) {
      render({
        success: false,
        provider: 'Unavailable',
        model: 'Unavailable',
        configured: false,
        message: 'Unable to check the Python AI service.',
      });
    } finally {
      setLoading(false);
    }
  };

  refreshButton.addEventListener('click', refreshHealth);
})();
