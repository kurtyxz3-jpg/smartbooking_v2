(() => {
  const form = document.getElementById('bookingForm');
  if (!form) return;

  const serviceNameInput = document.getElementById('serviceName');
  const staffIdInput = document.getElementById('staffId');
  const bookingTimeInput = document.getElementById('bookingTime');
  const dateInput = document.getElementById('bookingDate');

  const summaryService = document.getElementById('summaryService');
  const summaryStaff = document.getElementById('summaryStaff');
  const summaryDate = document.getElementById('summaryDate');
  const summaryTime = document.getElementById('summaryTime');
  const summaryDuration = document.getElementById('summaryDuration');
  const summaryPrice = document.getElementById('summaryPrice');
  const summaryImage = document.getElementById('summaryImage');
  const serviceSelectionMessage =
    document.getElementById('serviceSelectionMessage');

  const serviceCards = [...document.querySelectorAll('[data-service-card]')];
  const staffCards = [...document.querySelectorAll('[data-staff-card]')];
  const timeSlots = [...document.querySelectorAll('[data-time-slot]')];
  const progressButtons = [...document.querySelectorAll('[data-step-jump]')];
  const steps = [...document.querySelectorAll('[data-booking-step]')];

  let selectedService = null;
  let selectedStaff = {
    id: 0,
    name: 'Any Available Professional',
  };

  function money(value) {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 0,
    }).format(Number(value || 0));
  }

  function showStep(stepNumber) {
    steps.forEach((step) => {
      step.classList.toggle(
        'active',
        Number(step.dataset.bookingStep) === stepNumber
      );
    });

    progressButtons.forEach((button) => {
      const number = Number(button.dataset.stepJump);
      button.classList.toggle('active', number === stepNumber);
      button.classList.toggle('completed', number < stepNumber);
    });

    window.scrollTo({
      top: Math.max(0, form.getBoundingClientRect().top + window.scrollY - 110),
      behavior: 'smooth',
    });
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

    serviceNameInput.value = selectedService.name;
    summaryService.textContent = selectedService.name;
    summaryDuration.textContent = `~${selectedService.duration} minutes`;
    summaryPrice.textContent = money(selectedService.price);
    summaryImage.src = selectedService.image;
    summaryImage.alt = selectedService.name;

    if (serviceSelectionMessage) {
      serviceSelectionMessage.textContent =
        `${selectedService.name} selected · ${money(selectedService.price)}`;
    }
  }

  function chooseStaff(card) {
    staffCards.forEach((item) => item.classList.remove('selected'));
    card.classList.add('selected');

    selectedStaff = {
      id: Number(card.dataset.staffId),
      name: card.dataset.staffName,
    };

    staffIdInput.value = String(selectedStaff.id);
    summaryStaff.textContent = selectedStaff.name;

    if (dateInput.value) {
      loadAvailability();
    }
  }

  function selectTime(button) {
    timeSlots.forEach((slot) => slot.classList.remove('selected'));
    button.classList.add('selected');

    bookingTimeInput.value = button.dataset.timeSlot;
    summaryTime.textContent = button.textContent.trim();
  }

  async function loadAvailability() {
    const note = document.getElementById('availabilityNote');

    bookingTimeInput.value = '';
    summaryTime.textContent = 'Not selected';

    timeSlots.forEach((slot) => {
      slot.classList.remove('selected');
      slot.disabled = true;
    });

    if (!dateInput.value) {
      note.textContent = 'Select a date to load available slots.';
      return;
    }

    note.textContent = 'Checking current availability...';

    try {
      const query = new URLSearchParams({
        availability: '1',
        date: dateInput.value,
        staff_id: String(selectedStaff.id),
      });

      const response = await fetch(`book.php?${query.toString()}`, {
        headers: { Accept: 'application/json' },
      });

      const data = await response.json();
      const blocked = new Set(data.blocked || []);

      timeSlots.forEach((slot) => {
        slot.disabled = blocked.has(slot.dataset.timeSlot);
      });

      const available = timeSlots.filter((slot) => !slot.disabled).length;
      note.textContent =
        available > 0
          ? `${available} times currently available.`
          : 'No times are currently available for this selection.';
    } catch (error) {
      timeSlots.forEach((slot) => {
        slot.disabled = false;
      });

      note.textContent =
        'Live availability could not be loaded. Choose a time and the salon will verify it.';
    }
  }

  serviceCards.forEach((card) => {
    card.addEventListener('click', () => chooseService(card));
  });

  staffCards.forEach((card) => {
    card.addEventListener('click', () => chooseStaff(card));
  });

  timeSlots.forEach((slot) => {
    slot.addEventListener('click', () => {
      if (!slot.disabled) selectTime(slot);
    });
  });

  dateInput?.addEventListener('change', () => {
    const value = dateInput.value;

    summaryDate.textContent = value
      ? new Intl.DateTimeFormat('en-PH', {
          weekday: 'short',
          month: 'long',
          day: 'numeric',
          year: 'numeric',
        }).format(new Date(`${value}T00:00:00`))
      : 'Not selected';

    loadAvailability();
  });

  document.querySelectorAll('[data-next-step]').forEach((button) => {
    button.addEventListener('click', () => {
      const next = Number(button.dataset.nextStep);

      if (next === 2 && !selectedService) {
        alert('Please select a service before continuing.');
        return;
      }

      showStep(next);
    });
  });

  document.querySelectorAll('[data-back-step]').forEach((button) => {
    button.addEventListener('click', () => {
      showStep(Number(button.dataset.backStep));
    });
  });

  progressButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const target = Number(button.dataset.stepJump);

      if (target > 1 && !selectedService) {
        alert('Please select a service first.');
        return;
      }

      showStep(target);
    });
  });

  document
    .querySelectorAll('#bookingCategoryPills [data-category]')
    .forEach((button) => {
      button.addEventListener('click', () => {
        document
          .querySelectorAll('#bookingCategoryPills [data-category]')
          .forEach((item) => item.classList.remove('active'));

        button.classList.add('active');
        const category = button.dataset.category;

        serviceCards.forEach((card) => {
          card.hidden =
            category !== 'all' && card.dataset.category !== category;
        });
      });
    });

  document
    .getElementById('bookingServiceSearch')
    ?.addEventListener('input', (event) => {
      const term = event.target.value.trim().toLowerCase();

      document
        .querySelectorAll('#bookingCategoryPills [data-category]')
        .forEach((item) => {
          item.classList.toggle(
            'active',
            item.dataset.category === 'all'
          );
        });

      serviceCards.forEach((card) => {
        card.hidden = !card.dataset.search.includes(term);
      });
    });

  form.addEventListener('submit', (event) => {
    if (!selectedService) {
      event.preventDefault();
      showStep(1);
      alert('Please select a service.');
      return;
    }

    if (!dateInput.value) {
      event.preventDefault();
      showStep(3);
      alert('Please select an appointment date.');
      return;
    }

    if (!bookingTimeInput.value) {
      event.preventDefault();
      showStep(3);
      alert('Please select an available appointment time.');
    }
  });

  const initial = window.NBW_BOOKING_INITIAL_SERVICE;

  if (initial?.name) {
    const initialCard = serviceCards.find(
      (card) => card.dataset.name === initial.name
    );

    if (initialCard) {
      chooseService(initialCard);
    }
  }

  const draft = window.NBW_BOOKING_DRAFT || {};
  if (draft.staff_id !== undefined) {
    const draftStaff = staffCards.find(card => Number(card.dataset.staffId) === Number(draft.staff_id));
    if (draftStaff) chooseStaff(draftStaff);
  }
  if (draft.booking_date && dateInput) {
    dateInput.value = draft.booking_date;
    dateInput.dispatchEvent(new Event('change'));
    if (draft.booking_time) {
      window.setTimeout(() => {
        const draftSlot = timeSlots.find(slot => slot.dataset.timeSlot === String(draft.booking_time).slice(0,5));
        if (draftSlot && !draftSlot.disabled) selectTime(draftSlot);
      }, 500);
    }
  }

  const selectedStaffCard = staffCards.find(
    (card) => card.classList.contains('selected')
  );

  if (selectedStaffCard) {
    chooseStaff(selectedStaffCard);
  }
})();
