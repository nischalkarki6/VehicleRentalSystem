document.addEventListener("DOMContentLoaded", () => {
  document.querySelector("[data-history-back]")?.addEventListener("click", (event) => {
    event.preventDefault();

    if (history.length > 1) {
      history.back();
      return;
    }

    window.location.href = event.currentTarget.href;
  });

  const startDate = document.getElementById("start_date");
  const endDate = document.getElementById("end_date");
  const dailyRateInput = document.getElementById("daily_rate");

  if (startDate && endDate && dailyRateInput) {
    const dailyRate = parseFloat(dailyRateInput.value);
    const insuranceFee = dailyRate > 4500 ? 3300 : 0;

    function updateReceipt() {
      const start = new Date(startDate.value);
      const end = new Date(endDate.value);

      if (end <= start) {
        return;
      }

      const diffTime = Math.abs(end - start);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      const rentalCost = diffDays * dailyRate;
      const totalCost = rentalCost + insuranceFee;

      document.getElementById("rental-days-label").textContent =
        `Rental (${diffDays} day${diffDays > 1 ? "s" : ""})`;
      document.getElementById("rental-cost-val").textContent =
        `Rs. ${rentalCost.toLocaleString("en-US", { maximumFractionDigits: 0 })}`;
      document.getElementById("total-cost-val").textContent =
        `Rs. ${totalCost.toLocaleString("en-US", { maximumFractionDigits: 0 })}`;
    }

    startDate.addEventListener("change", () => {
      if (new Date(endDate.value) <= new Date(startDate.value)) {
        const nextDay = new Date(startDate.value);
        nextDay.setDate(nextDay.getDate() + 1);
        endDate.value = nextDay.toISOString().split("T")[0];
      }

      updateReceipt();
    });

    endDate.addEventListener("change", updateReceipt);
    updateReceipt();
  }

  /* ── Terms & Conditions Modal ─────────────────────────── */
  const bookingForm   = document.getElementById("bookingForm");
  const openBtn       = document.getElementById("openTncModal");
  const overlay       = document.getElementById("tncModal");
  const closeBtn      = document.getElementById("closeTncModal");
  const disagreeBtn   = document.getElementById("disagreeTnc");
  const agreeBtn      = document.getElementById("agreeTnc");
  const checkbox      = document.getElementById("agreeCheckbox");
  const checkLabel    = checkbox?.closest(".tnc-checkbox-label");
  const errorMsg      = document.getElementById("tncError");

  if (!openBtn || !overlay || !bookingForm) return;

  function openModal() {
    overlay.classList.add("active");
    overlay.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    if (checkbox) checkbox.checked = false;
    if (checkLabel) checkLabel.classList.remove("checked");
    if (errorMsg) errorMsg.textContent = "";
  }

  function closeModal() {
    overlay.classList.remove("active");
    overlay.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  openBtn.addEventListener("click", () => {
    if (!bookingForm.reportValidity()) return;
    openModal();
  });

  closeBtn?.addEventListener("click", closeModal);
  disagreeBtn?.addEventListener("click", closeModal);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("active")) closeModal();
  });

  checkbox?.addEventListener("change", () => {
    if (checkLabel) {
      checkLabel.classList.toggle("checked", checkbox.checked);
    }
    if (checkbox.checked && errorMsg) errorMsg.textContent = "";
  });

  agreeBtn?.addEventListener("click", () => {
    if (!checkbox?.checked) {
      if (errorMsg) errorMsg.textContent = "Please tick the checkbox to agree to the Terms & Conditions.";
      checkbox?.closest(".tnc-agreement")?.scrollIntoView({ behavior: "smooth", block: "center" });
      return;
    }
    closeModal();
    bookingForm.submit();
  });
});
