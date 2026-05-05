// ── admin.js ──────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  // ── Toggle Add/Edit Vehicle form ────────────────────────────────────────────
  const addForm = document.getElementById("addVehicleForm");

  window.toggleAddForm = function () {
    if (!addForm) return;
    
    const isHidden = addForm.style.display === "none" || addForm.classList.contains("d-none");
    
    // If opening, reset for Add
    if (isHidden) {
        const form = document.getElementById("vehicleForm");
        if (form) {
            form.reset();
            form.querySelector('[name="action"]').value = "add_vehicle";
            addForm.querySelector("h3").textContent = "Add New Vehicle";
            const vidInput = form.querySelector('[name="vehicle_id"]');
            if (vidInput) vidInput.remove();
        }
        // Hide image preview when switching to Add mode
        const preview = document.getElementById("currentImagePreview");
        if (preview) preview.classList.add("d-none");
        
        addForm.classList.remove("d-none");
        addForm.style.display = "block";
    } else {
        addForm.style.display = "none";
        addForm.classList.add("d-none");
    }
  };

  window.editVehicle = function(vehicle) {
    if (!addForm) return;
    const form = document.getElementById("vehicleForm");
    
    // Change title
    addForm.querySelector("h3").textContent = "Edit Vehicle";
    
    // Set inputs
    form.querySelector('[name="action"]').value = "edit_vehicle";
    
    // Add vehicle_id hidden input if not exists
    let vidInput = form.querySelector('[name="vehicle_id"]');
    if (!vidInput) {
        vidInput = document.createElement("input");
        vidInput.type = "hidden";
        vidInput.name = "vehicle_id";
        form.appendChild(vidInput);
    }
    vidInput.value = vehicle.VehicleID;
    
    // Populate fields
    if (form.querySelector('[name="name"]')) form.querySelector('[name="name"]').value = vehicle.Name || "";
    if (form.querySelector('[name="category"]')) form.querySelector('[name="category"]').value = vehicle.Category || "";
    if (form.querySelector('[name="type"]')) form.querySelector('[name="type"]').value = vehicle.Type || "";
    if (form.querySelector('[name="transmission"]')) form.querySelector('[name="transmission"]').value = vehicle.Transmission || "";
    if (form.querySelector('[name="daily_rate"]')) form.querySelector('[name="daily_rate"]').value = vehicle.DailyRate || "";

    // Reset file input (can't programmatically set file inputs)
    const fileInput = form.querySelector('[name="image"]');
    if (fileInput) fileInput.value = "";

    // Show current image preview if vehicle has an image
    const preview = document.getElementById("currentImagePreview");
    const thumb   = document.getElementById("currentImageThumb");
    if (preview && thumb) {
        if (vehicle.ImageURL && vehicle.ImageURL.trim() !== "") {
            thumb.src = vehicle.ImageURL;
            preview.classList.remove("d-none");
        } else {
            thumb.src = "";
            preview.classList.add("d-none");
        }
    }
    
    addForm.classList.remove("d-none");
    addForm.style.display = "block";
    window.scrollTo({ top: addForm.offsetTop - 50, behavior: 'smooth' });
  };

  // Auto-open form if PHP flagged validation errors
  if (addForm && addForm.dataset.hasErrors === "1") {
    addForm.classList.remove("d-none");
    addForm.style.display = "block";
  }

  // ── Live image preview when a new file is selected ─────────────────────────
  const imgInput = document.getElementById("vehicleImageInput");
  if (imgInput) {
    imgInput.addEventListener("change", function () {
      const preview = document.getElementById("currentImagePreview");
      const thumb   = document.getElementById("currentImageThumb");
      if (!preview || !thumb) return;

      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          thumb.src = e.target.result;
          preview.classList.remove("d-none");
          // Update label to "New Image:"
          const label = preview.querySelector("label");
          if (label) label.textContent = "New Image:";
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
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
