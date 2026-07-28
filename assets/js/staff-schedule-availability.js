(() => {
  const endpoint = window.NBW_SCHEDULE_ENDPOINT;
  if (!endpoint) return;

  const toast = (message, type = 'success') => {
    let node = document.getElementById('scheduleToast');
    if (!node) {
      node = document.createElement('div');
      node.id = 'scheduleToast';
      node.className = 'schedule-toast';
      node.setAttribute('role', 'status');
      document.body.appendChild(node);
    }
    node.className = `schedule-toast ${type} show`;
    node.textContent = message;
    setTimeout(() => node.classList.remove('show'), 3500);
  };

  const send = async (payload) => {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
    });
    const responseText = await response.text();
    let data;
    try {
      data = JSON.parse(responseText);
    } catch {
      data = {
        success: false,
        message: 'The schedule server returned an invalid response.',
      };
    }
    if (!response.ok || !data.success) throw new Error(data.message || 'Request failed.');
    return data;
  };

  document.querySelectorAll('[data-working-toggle]').forEach((input) => {
    const update = () => {
      const card = input.closest('[data-weekly-day]');
      card.classList.toggle('is-off', !input.checked);
      card.querySelector('[data-day-state]').textContent =
        input.checked ? 'Working day' : 'Day off';
      card.querySelectorAll('input[type="time"]').forEach((time) => {
        time.disabled = !input.checked;
      });
    };
    input.addEventListener('change', update);
    update();
  });

  const weeklyForm = document.getElementById('weeklyScheduleForm');
  weeklyForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = weeklyForm.querySelector('button[type="submit"]');
    const form = new FormData(weeklyForm);
    const days = {};
    for (let day = 1; day <= 7; day += 1) {
      days[day] = {
        is_working: form.get(`days[${day}][is_working]`) === '1',
        start_time: form.get(`days[${day}][start_time]`) || '',
        end_time: form.get(`days[${day}][end_time]`) || '',
        break_start: form.get(`days[${day}][break_start]`) || '',
        break_end: form.get(`days[${day}][break_end]`) || '',
      };
    }
    button.disabled = true;
    try {
      const data = await send({
        action: 'save_weekly',
        csrf_token: form.get('csrf_token'),
        staff_id: form.get('staff_id'),
        days,
      });
      toast(data.message);
    } catch (error) {
      toast(error.message, 'error');
    } finally {
      button.disabled = false;
    }
  });

  const exceptionForm = document.getElementById('exceptionForm');
  const typeSelect = document.getElementById('exceptionType');
  const openException = document.getElementById('openExceptionForm');
  const cancelException = document.getElementById('cancelException');
  const exceptionMessage = document.querySelector('[data-exception-message]');

  const showExceptionMessage = (message, type = 'success') => {
    if (!exceptionMessage) return;
    exceptionMessage.textContent = message;
    exceptionMessage.className =
      `schedule-form-message${type === 'error' ? ' error' : ''}`;
    exceptionMessage.hidden = false;
  };

  const clearExceptionMessage = () => {
    if (!exceptionMessage) return;
    exceptionMessage.hidden = true;
    exceptionMessage.textContent = '';
    exceptionMessage.className = 'schedule-form-message';
  };

  const updateExceptionTimes = () => {
    const hidden = typeSelect?.value === 'day_off';
    document.querySelectorAll('[data-exception-time]').forEach((field) => {
      field.hidden = hidden;
      field.querySelector('input').disabled = hidden;
    });
  };
  typeSelect?.addEventListener('change', updateExceptionTimes);
  updateExceptionTimes();

  openException?.addEventListener('click', () => {
    clearExceptionMessage();
    exceptionForm.hidden = false;
    openException.setAttribute('aria-expanded', 'true');
    updateExceptionTimes();
    exceptionForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    exceptionForm.querySelector('input[name="exception_date"]')?.focus();
  });

  cancelException?.addEventListener('click', () => {
    exceptionForm.reset();
    exceptionForm.hidden = true;
    openException?.setAttribute('aria-expanded', 'false');
    clearExceptionMessage();
    updateExceptionTimes();
    openException?.focus();
  });

  exceptionForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!exceptionForm.reportValidity()) {
      showExceptionMessage(
        'Complete the required date and schedule information.',
        'error'
      );
      return;
    }

    clearExceptionMessage();
    const form = new FormData(exceptionForm);
    const button = exceptionForm.querySelector('button[type="submit"]');
    const originalLabel = button.textContent;
    button.disabled = true;
    button.textContent = 'Saving...';
    try {
      const data = await send({
        action: 'add_exception',
        csrf_token: form.get('csrf_token'),
        staff_id: form.get('staff_id'),
        exception_date: form.get('exception_date'),
        exception_type: form.get('exception_type'),
        start_time: form.get('start_time'),
        end_time: form.get('end_time'),
        reason: form.get('reason'),
      });
      toast(data.message);
      showExceptionMessage(data.message);
      window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      toast(error.message, 'error');
      showExceptionMessage(error.message, 'error');
    } finally {
      button.disabled = false;
      button.textContent = originalLabel;
    }
  });

  document.querySelectorAll('[data-delete-exception]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!window.confirm('Remove this schedule exception?')) return;
      const form = new FormData(document.getElementById('weeklyScheduleForm'));
      button.disabled = true;
      try {
        const data = await send({
          action: 'delete_exception',
          csrf_token: form.get('csrf_token'),
          staff_id: form.get('staff_id'),
          exception_id: button.dataset.deleteException,
        });
        toast(data.message);
        button.closest('.exception-row')?.remove();
      } catch (error) {
        toast(error.message, 'error');
        button.disabled = false;
      }
    });
  });

  document.getElementById('scheduleStaffSearch')?.addEventListener('input', (event) => {
    const search = event.target.value.trim().toLowerCase();
    document.querySelectorAll('[data-staff-search]').forEach((card) => {
      card.hidden = search !== '' && !card.dataset.staffSearch.includes(search);
    });
  });
})();
