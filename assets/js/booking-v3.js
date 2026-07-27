(() => {
  const form = document.getElementById('bookingForm');
  if (!form) return;

  const branchInput = document.getElementById('branchId');
  const serviceInput = document.getElementById('serviceName');
  const staffInput = document.getElementById('staffId');
  const timeInput = document.getElementById('bookingTime');
  const dateInput = document.getElementById('bookingDate');
  const branchCards = [...document.querySelectorAll('[data-branch-card]')];
  const serviceCards = [...document.querySelectorAll('[data-service-card]')];
  const staffCards = [...document.querySelectorAll('[data-staff-card]')];
  const timeSlots = [...document.querySelectorAll('[data-time-slot]')];
  const progressButtons = [...document.querySelectorAll('[data-step-jump]')];
  const steps = [...document.querySelectorAll('[data-booking-step]')];

  const ui = {
    branchMessage: document.getElementById('branchSelectionMessage'),
    serviceMessage: document.getElementById('serviceSelectionMessage'),
    summaryBranch: document.getElementById('summaryBranch'),
    summaryService: document.getElementById('summaryService'),
    summaryStaff: document.getElementById('summaryStaff'),
    summaryDate: document.getElementById('summaryDate'),
    summaryTime: document.getElementById('summaryTime'),
    summaryDuration: document.getElementById('summaryDuration'),
    summaryPrice: document.getElementById('summaryPrice'),
    summaryImage: document.getElementById('summaryImage'),
    reviewBranch: document.getElementById('reviewBranch'),
    reviewService: document.getElementById('reviewService'),
    reviewStaff: document.getElementById('reviewStaff'),
    reviewSchedule: document.getElementById('reviewSchedule'),
    availabilityNote: document.getElementById('availabilityNote'),
  };

  let selectedBranch = null;
  let selectedService = null;
  let selectedStaff = { id: 0, name: 'Any Available Professional' };

  const money = (value) =>
    new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 0,
    }).format(Number(value || 0));

  function notify(message, target) {
    let alert = document.getElementById('bookingInlineAlert');
    if (!alert) {
      alert = document.createElement('div');
      alert.id = 'bookingInlineAlert';
      alert.className = 'booking-inline-alert';
      alert.setAttribute('role', 'alert');
      form.prepend(alert);
    }
    alert.textContent = message;
    alert.hidden = false;
    target?.focus?.();
    window.setTimeout(() => { alert.hidden = true; }, 4500);
  }

  function showStep(number, scroll = true) {
    steps.forEach((step) => {
      step.classList.toggle('active', Number(step.dataset.bookingStep) === number);
    });
    progressButtons.forEach((button) => {
      const step = Number(button.dataset.stepJump);
      button.classList.toggle('active', step === number);
      button.classList.toggle('completed', step < number);
      button.setAttribute('aria-current', step === number ? 'step' : 'false');
    });
    if (scroll) {
      window.scrollTo({
        top: Math.max(0, form.getBoundingClientRect().top + window.scrollY - 112),
        behavior: 'smooth',
      });
    }
  }

  function chooseBranch(card, reload = false) {
    branchCards.forEach((item) => item.classList.remove('selected'));
    card.classList.add('selected');
    selectedBranch = {
      id: Number(card.dataset.branchId),
      name: card.dataset.branchName,
    };
    branchInput.value = String(selectedBranch.id);
    ui.branchMessage.textContent = `${selectedBranch.name} selected`;
    ui.summaryBranch.textContent = selectedBranch.name;
    ui.reviewBranch.textContent = selectedBranch.name;

    if (reload && Number(card.dataset.branchId) !== Number(window.NBW_BOOKING_BRANCH_ID)) {
      const query = new URLSearchParams({
        branch_id: String(selectedBranch.id),
        service: serviceInput.value,
        step: '2',
      });
      window.location.assign(`book.php?${query.toString()}`);
    }
  }

  function chooseService(card) {
    serviceCards.forEach((item) => item.classList.remove('selected'));
    card.classList.add('selected');
    selectedService = {
      name: card.dataset.name,
      price: Number(card.dataset.price),
      duration: Number(card.dataset.duration),
      image: card.dataset.image,
    };
    serviceInput.value = selectedService.name;
    ui.summaryService.textContent = selectedService.name;
    ui.reviewService.textContent = selectedService.name;
    ui.summaryDuration.textContent = `~${selectedService.duration} minutes`;
    ui.summaryPrice.textContent = money(selectedService.price);
    ui.summaryImage.src = selectedService.image;
    ui.summaryImage.alt = selectedService.name;
    ui.serviceMessage.textContent = `${selectedService.name} selected · ${money(selectedService.price)}`;
  }

  function chooseStaff(card) {
    staffCards.forEach((item) => item.classList.remove('selected'));
    card.classList.add('selected');
    selectedStaff = {
      id: Number(card.dataset.staffId),
      name: card.dataset.staffName,
    };
    staffInput.value = String(selectedStaff.id);
    ui.summaryStaff.textContent = selectedStaff.name;
    ui.reviewStaff.textContent = selectedStaff.name;
    if (dateInput.value) loadAvailability();
  }

  function selectTime(button) {
    timeSlots.forEach((slot) => slot.classList.remove('selected'));
    button.classList.add('selected');
    timeInput.value = button.dataset.timeSlot;
    ui.summaryTime.textContent = button.textContent.trim();
    updateReviewSchedule();
  }

  function updateReviewSchedule() {
    const date = ui.summaryDate.textContent;
    const time = ui.summaryTime.textContent;
    ui.reviewSchedule.textContent =
      date !== 'Not selected' && time !== 'Not selected'
        ? `${date} · ${time}`
        : 'Not selected';
  }

  async function loadAvailability() {
    timeInput.value = '';
    ui.summaryTime.textContent = 'Not selected';
    updateReviewSchedule();
    timeSlots.forEach((slot) => {
      slot.classList.remove('selected');
      slot.disabled = true;
      slot.classList.remove('is-loading');
    });

    if (!dateInput.value) {
      ui.availabilityNote.textContent = 'Select a date to load available slots.';
      return;
    }

    ui.availabilityNote.textContent = 'Checking current availability…';
    timeSlots.forEach((slot) => slot.classList.add('is-loading'));

    try {
      const query = new URLSearchParams({
        availability: '1',
        date: dateInput.value,
        staff_id: String(selectedStaff.id),
        branch_id: branchInput.value,
      });
      const response = await fetch(`book.php?${query.toString()}`, {
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) throw new Error('Availability request failed');
      const data = await response.json();
      const blocked = new Set(data.blocked || []);
      timeSlots.forEach((slot) => {
        slot.classList.remove('is-loading');
        slot.disabled = blocked.has(slot.dataset.timeSlot);
      });
      const available = timeSlots.filter((slot) => !slot.disabled).length;
      ui.availabilityNote.textContent = available
        ? `${available} times currently available.`
        : 'No times are available for this selection. Try another date or professional.';
    } catch (error) {
      timeSlots.forEach((slot) => {
        slot.classList.remove('is-loading');
        slot.disabled = false;
      });
      ui.availabilityNote.textContent =
        'Live availability could not be loaded. Your chosen time will be checked on submission.';
    }
  }

  function canEnterStep(target) {
    if (target > 1 && !selectedBranch) {
      notify('Please select a salon branch first.', branchCards[0]);
      showStep(1);
      return false;
    }
    if (target > 2 && !selectedService) {
      notify('Please select a service before continuing.', serviceCards[0]);
      showStep(2);
      return false;
    }
    if (target > 4 && (!dateInput.value || !timeInput.value)) {
      notify('Please select an appointment date and available time.', dateInput);
      showStep(4);
      return false;
    }
    return true;
  }

  branchCards.forEach((card) => card.addEventListener('click', () => chooseBranch(card, true)));
  serviceCards.forEach((card) => card.addEventListener('click', () => chooseService(card)));
  staffCards.forEach((card) => card.addEventListener('click', () => chooseStaff(card)));
  timeSlots.forEach((slot) =>
    slot.addEventListener('click', () => !slot.disabled && selectTime(slot))
  );

  dateInput?.addEventListener('change', () => {
    ui.summaryDate.textContent = dateInput.value
      ? new Intl.DateTimeFormat('en-PH', {
          weekday: 'short',
          month: 'long',
          day: 'numeric',
          year: 'numeric',
        }).format(new Date(`${dateInput.value}T00:00:00`))
      : 'Not selected';
    updateReviewSchedule();
    loadAvailability();
  });

  document.querySelectorAll('[data-next-step]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = Number(button.dataset.nextStep);
      if (canEnterStep(target)) showStep(target);
    });
  });
  document.querySelectorAll('[data-back-step]').forEach((button) =>
    button.addEventListener('click', () => showStep(Number(button.dataset.backStep)))
  );
  progressButtons.forEach((button) =>
    button.addEventListener('click', () => {
      const target = Number(button.dataset.stepJump);
      if (canEnterStep(target)) showStep(target);
    })
  );

  document.querySelectorAll('#bookingCategoryPills [data-category]').forEach((button) => {
    button.addEventListener('click', () => {
      document.querySelectorAll('#bookingCategoryPills [data-category]')
        .forEach((item) => item.classList.remove('active'));
      button.classList.add('active');
      serviceCards.forEach((card) => {
        card.hidden =
          button.dataset.category !== 'all' &&
          card.dataset.category !== button.dataset.category;
      });
    });
  });

  document.getElementById('bookingServiceSearch')?.addEventListener('input', (event) => {
    const term = event.target.value.trim().toLowerCase();
    document.querySelectorAll('#bookingCategoryPills [data-category]').forEach((item) =>
      item.classList.toggle('active', item.dataset.category === 'all')
    );
    serviceCards.forEach((card) => {
      card.hidden = !card.dataset.search.includes(term);
    });
  });

  form.addEventListener('submit', (event) => {
    if (!canEnterStep(5)) event.preventDefault();
  });

  const activeBranchCard = branchCards.find((card) =>
    Number(card.dataset.branchId) === Number(branchInput.value)
  );
  if (activeBranchCard) chooseBranch(activeBranchCard);

  const initial = window.NBW_BOOKING_INITIAL_SERVICE;
  if (initial?.name) {
    const card = serviceCards.find((item) => item.dataset.name === initial.name);
    if (card) chooseService(card);
  }

  const draft = window.NBW_BOOKING_DRAFT || {};
  const draftStaff = staffCards.find((card) =>
    Number(card.dataset.staffId) === Number(draft.staff_id || 0)
  );
  chooseStaff(draftStaff || staffCards[0]);

  if (draft.booking_date && dateInput) {
    dateInput.value = draft.booking_date;
    dateInput.dispatchEvent(new Event('change'));
    if (draft.booking_time) {
      window.setTimeout(() => {
        const slot = timeSlots.find((item) =>
          item.dataset.timeSlot === String(draft.booking_time).slice(0, 5)
        );
        if (slot && !slot.disabled) selectTime(slot);
      }, 550);
    }
  }

  const requestedStep = Number(new URLSearchParams(window.location.search).get('step'));
  showStep(requestedStep >= 1 && requestedStep <= 5 ? requestedStep : 1, false);
})();
