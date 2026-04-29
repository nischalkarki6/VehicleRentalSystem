// ── admin.js ──────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  // ── Toggle Add Vehicle form ─────────────────────────────────────────────────
  const addForm = document.getElementById("addVehicleForm");

  window.toggleAddForm = function () {
    if (!addForm) return;
    addForm.style.display = addForm.style.display === "none" ? "block" : "none";
  };

  // Auto-open form if PHP flagged validation errors
  if (addForm && addForm.dataset.hasErrors === "1") {
    addForm.style.display = "block";
  }

  // ── Live table search ───────────────────────────────────────────────────────
  window.filterTable = function (tableId, query) {
    const rows = document.querySelectorAll("#" + tableId + " tbody tr");
    query = query.toLowerCase();
    rows.forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(query)
        ? ""
        : "none";
    });
  };

  // ── Filter bookings by status ───────────────────────────────────────────────
  window.filterByStatus = function () {
    const statusEl = document.getElementById("statusFilter");
    const searchEl = document.getElementById("bookingSearch");
    const status = statusEl ? statusEl.value : "";
    const search = searchEl ? searchEl.value.toLowerCase() : "";

    document.querySelectorAll("#bookingsTable tbody tr").forEach((row) => {
      const matchStatus = !status || row.dataset.status === status;
      const matchSearch =
        !search || row.textContent.toLowerCase().includes(search);
      row.style.display = matchStatus && matchSearch ? "" : "none";
    });
  };

  // Wire up bookingSearch input to also trigger status filter
  const bookingSearch = document.getElementById("bookingSearch");
  if (bookingSearch) {
    bookingSearch.addEventListener("input", window.filterByStatus);
  }

  // ── Confirm destructive actions ─────────────────────────────────────────────
  document.querySelectorAll(".confirm-delete").forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!confirm("Are you sure? This cannot be undone.")) e.preventDefault();
    });
  });
});
