document.addEventListener("DOMContentLoaded", () => {
  const startInput = document.getElementById("start_date");
  const endInput = document.getElementById("end_date");
  const totalDisplay = document.getElementById("estimated-total");
  const durationDisplay = document.getElementById("duration-display");

  // Try retrieving the daily rate from the injected hidden div or form element
  const rateElement = document.getElementById("vehicle-daily-rate");
  if (!rateElement) return;

  const dailyRate = parseFloat(rateElement.value);

  function calculateTotal() {
    const start = new Date(startInput.value);
    const end = new Date(endInput.value);
    if (end > start) {
      const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24));
      const total = diffDays * dailyRate;
      if (totalDisplay) {
        totalDisplay.textContent =
          "NPR " +
          total.toLocaleString(undefined, { minimumFractionDigits: 2 });
      }
      if (durationDisplay) {
        durationDisplay.textContent =
          diffDays + (diffDays === 1 ? " day" : " days");
      }
    }
  }

  if (startInput && endInput) {
    // Sync end date min when start date changes
    startInput.addEventListener("change", () => {
      const nextDay = new Date(startInput.value);
      nextDay.setDate(nextDay.getDate() + 1);
      endInput.min = nextDay.toISOString().split("T")[0];
      if (endInput.value <= startInput.value) {
        endInput.value = nextDay.toISOString().split("T")[0];
      }
      calculateTotal();
    });

    endInput.addEventListener("change", calculateTotal);

    // Initial calculation on load
    calculateTotal();
  }
});
