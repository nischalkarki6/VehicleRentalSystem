document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#vehicle-tabs .tab");
  const searchBtn = document.getElementById("main-search-btn");
  const pickupInput = document.getElementById("pickup-location");
  const dropoffInput = document.getElementById("dropoff-location");
  const dateInput = document.getElementById("pickup-date");
  const travelInput = document.getElementById("travel-location");

  let selectedType = "4-wheeler";

  // Tab Switching
  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabs.forEach((t) => t.classList.remove("active"));

      tab.classList.add("active");
      selectedType = tab.dataset.type;
    });
  });

  // Search Functionality
  if (searchBtn) {
    searchBtn.addEventListener("click", () => {
      const pickup = pickupInput.value.trim();
      const date = dateInput.value;

      if (!pickup || !date) {
        if (!pickup) pickupInput.focus();
        else dateInput.focus();
        return;
      }

      const dropoff = dropoffInput.value.trim() || pickup;
      const travel = travelInput.value.trim() || pickup;

      const targetPage = "search.php";
      const queryParams = new URLSearchParams({
        type: selectedType,
        pickup,
        dropoff,
        date,
        travel,
      });

      window.location.href = `${targetPage}?${queryParams.toString()}`;
    });
  }

  // Set default date to today
  if (dateInput) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);
    dateInput.value = today;
  }
});
