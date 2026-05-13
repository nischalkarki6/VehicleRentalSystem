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

  if (!startDate || !endDate || !dailyRateInput) {
    return;
  }

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
});
