(() => {
  "use strict";

  const config = window.SmartBookingManagement || {};
  const modal = document.getElementById("bookingModal");
  const content = modal?.querySelector("[data-booking-content]");
  const loading = modal?.querySelector("[data-booking-loading]");
  const updateForm = document.getElementById("bookingUpdateForm");
  const statusSelect = modal?.querySelector("[data-booking-status]");
  const staffSelect = modal?.querySelector("[data-booking-staff]");
  const idInput = modal?.querySelector("[data-booking-id-input]");
  const message = modal?.querySelector("[data-booking-message]");
  let activeBookingId = null;

  const escapeHtml = (value) => String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");

  const money = (value) => new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP"
  }).format(Number(value || 0));

  const formatDateTime = (date, time) => {
    const parsed = new Date(`${date}T${time || "00:00:00"}`);
    return Number.isNaN(parsed.getTime())
      ? `${date} ${time}`
      : parsed.toLocaleString("en-PH", {
          dateStyle: "long",
          timeStyle: "short"
        });
  };

  const statusLabel = (status) => String(status || "")
    .replaceAll("_", " ")
    .replace(/\b\w/g, (letter) => letter.toUpperCase());

  const setDetail = (name, value) => {
    const target = modal?.querySelector(`[data-detail="${name}"]`);
    if (target) target.textContent = value || "—";
  };

  const showMessage = (text, isError = false) => {
    if (!message) return;
    message.textContent = text;
    message.classList.toggle("error", isError);
    message.hidden = false;
  };

  const renderHistory = (booking) => {
    const timeline = modal?.querySelector("[data-booking-timeline]");
    if (!timeline) return;
    const history = Array.isArray(booking.history) ? booking.history : [];
    const fallback = [{
      to_status: booking.status,
      created_at: booking.created_at,
      changed_by_name: "System",
      note: "Current booking status"
    }];
    timeline.innerHTML = (history.length ? history : fallback).map((item) => `
      <li>
        <strong>${escapeHtml(statusLabel(item.to_status))}</strong>
        <small>${escapeHtml(formatDateTime(
          String(item.created_at || "").slice(0, 10),
          String(item.created_at || "").slice(11, 19)
        ))}</small>
        <span>${escapeHtml(item.note || `Updated by ${item.changed_by_name || "System"}`)}</span>
      </li>
    `).join("");
  };

  const renderBooking = (booking) => {
    activeBookingId = Number(booking.id);
    modal.querySelector("#bookingModalTitle").textContent =
      booking.reference || `Booking #${booking.id}`;
    setDetail("customer_name", booking.customer_name || "Guest customer");
    setDetail("customer_email", booking.customer_email);
    setDetail("customer_phone", booking.customer_phone);
    setDetail("service_name", `${booking.service_name || "Salon service"} · ${money(booking.service_price)}`);
    setDetail("branch_name", booking.branch_name);
    setDetail("schedule", formatDateTime(booking.booking_date, booking.booking_time));
    setDetail("notes", booking.notes || "No special instructions.");
    setDetail("payment_status", statusLabel(booking.payment_status || "not submitted"));
    setDetail("payment_amount", booking.payment_id ? money(booking.payment_amount) : "—");
    setDetail("payment_method", booking.payment_method);
    setDetail("reference_number", booking.reference_number);

    const aiCard = modal.querySelector("[data-ai-design-card]");
    const aiLink = modal.querySelector("[data-ai-design-link]");
    const hasAiDesign = Number(booking.selected_ai_design_id || 0) > 0;
    if (aiCard) aiCard.hidden = !hasAiDesign;
    if (aiLink && hasAiDesign) {
      aiLink.href = `${config.aiDesignUrl}${encodeURIComponent(booking.id)}`;
    }

    idInput.value = booking.id;
    staffSelect.value = booking.staff_id || "";
    const statuses = [booking.status, ...(booking.allowed_transitions || [])];
    statusSelect.innerHTML = [...new Set(statuses)].map((status) =>
      `<option value="${escapeHtml(status)}">${escapeHtml(statusLabel(status))}</option>`
    ).join("");
    statusSelect.value = booking.status;
    updateForm.querySelector('[name="note"]').value = "";
    message.hidden = true;
    renderHistory(booking);
  };

  const loadBooking = async (bookingId) => {
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    loading.innerHTML = "<span></span> Loading booking details…";
    loading.hidden = false;
    content.hidden = true;
    try {
      const response = await fetch(`${config.detailsUrl}?id=${encodeURIComponent(bookingId)}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || "Unable to load the booking.");
      }
      renderBooking(result.booking);
      content.hidden = false;
    } catch (error) {
      loading.innerHTML = `<div class="booking-empty-state"><strong>Booking unavailable</strong><span>${escapeHtml(error.message)}</span></div>`;
    } finally {
      if (!loading.querySelector(".booking-empty-state")) loading.hidden = true;
    }
  };

  const closeModal = () => {
    modal?.classList.remove("open");
    modal?.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  document.addEventListener("click", (event) => {
    const manage = event.target.closest("[data-manage-booking]");
    if (manage) loadBooking(manage.dataset.manageBooking);
    if (event.target.closest("[data-booking-close]")) closeModal();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal?.classList.contains("open")) closeModal();
  });

  updateForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    const submit = updateForm.querySelector('[type="submit"]');
    submit.disabled = true;
    submit.textContent = "Saving…";
    message.hidden = true;
    try {
      const response = await fetch(config.updateUrl, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: new FormData(updateForm)
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || "Unable to update the booking.");
      }
      showMessage(result.message);
      const row = document.querySelector(`[data-booking-row][data-booking-id="${activeBookingId}"]`);
      if (row) {
        row.querySelector(".booking-status").className =
          `booking-status ${result.booking.status}`;
        row.querySelector(".booking-status").textContent = result.booking.status_label;
        row.children[3].textContent = result.booking.staff_name || "Unassigned";
      }
      setTimeout(() => loadBooking(activeBookingId), 450);
    } catch (error) {
      showMessage(error.message, true);
    } finally {
      submit.disabled = false;
      submit.textContent = "Save changes";
    }
  });

  const searchInput = document.querySelector("[data-booking-live-search]");
  searchInput?.addEventListener("input", () => {
    const query = searchInput.value.trim().toLowerCase();
    const rows = [...document.querySelectorAll("[data-booking-row]")];
    let visible = 0;
    rows.forEach((row) => {
      const match = !query || row.dataset.search.includes(query);
      row.hidden = !match;
      if (match) visible += 1;
    });
    const count = document.getElementById("bookingVisibleCount");
    const empty = document.getElementById("bookingClientEmpty");
    if (count) count.textContent = String(visible);
    if (empty) empty.hidden = visible !== 0;
  });

  const panels = document.querySelectorAll("[data-booking-panel]");
  const viewInput = document.getElementById("bookingViewInput");
  document.querySelectorAll("[data-booking-view]").forEach((button) => {
    button.addEventListener("click", () => {
      const view = button.dataset.bookingView;
      document.querySelectorAll("[data-booking-view]").forEach((item) =>
        item.classList.toggle("active", item === button));
      panels.forEach((panel) => { panel.hidden = panel.dataset.bookingPanel !== view; });
      if (viewInput) viewInput.value = view;
      if (view === "calendar") renderCalendar();
    });
  });

  let calendarDate = new Date();
  const renderCalendar = () => {
    const target = document.getElementById("bookingCalendar");
    const title = document.getElementById("bookingCalendarTitle");
    if (!target || !title) return;
    const year = calendarDate.getFullYear();
    const month = calendarDate.getMonth();
    title.textContent = calendarDate.toLocaleDateString("en-PH", {
      month: "long", year: "numeric"
    });
    const start = new Date(year, month, 1);
    start.setDate(start.getDate() - start.getDay());
    const today = new Date();
    const cells = [];
    for (let index = 0; index < 42; index += 1) {
      const day = new Date(start);
      day.setDate(start.getDate() + index);
      const key = [
        day.getFullYear(),
        String(day.getMonth() + 1).padStart(2, "0"),
        String(day.getDate()).padStart(2, "0")
      ].join("-");
      const events = (config.bookings || []).filter((booking) => booking.date === key);
      const classes = [
        "booking-calendar-day",
        day.getMonth() !== month ? "outside" : "",
        day.toDateString() === today.toDateString() ? "today" : ""
      ].filter(Boolean).join(" ");
      cells.push(`<div class="${classes}">
        <span class="booking-calendar-number">${day.getDate()}</span>
        ${events.slice(0, 4).map((booking) => `
          <button type="button" class="booking-calendar-event ${escapeHtml(booking.status)}"
            data-manage-booking="${booking.id}" title="${escapeHtml(booking.customer)}">
            ${escapeHtml(String(booking.time).slice(0, 5))} · ${escapeHtml(booking.customer)}
          </button>`).join("")}
        ${events.length > 4 ? `<small>+${events.length - 4} more</small>` : ""}
      </div>`);
    }
    target.innerHTML = cells.join("");
  };

  document.querySelectorAll("[data-calendar-shift]").forEach((button) => {
    button.addEventListener("click", () => {
      calendarDate = new Date(
        calendarDate.getFullYear(),
        calendarDate.getMonth() + Number(button.dataset.calendarShift),
        1
      );
      renderCalendar();
    });
  });

  if (!document.querySelector('[data-booking-panel="calendar"]')?.hidden) {
    renderCalendar();
  }
})();
