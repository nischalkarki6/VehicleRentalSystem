document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#vehicle-tabs .tab");
  const searchBtn = document.getElementById("main-search-btn");
  const pickupInput = document.getElementById("pickup-location");
  const dateInput = document.getElementById("pickup-date");
  const travelInput = document.getElementById("travel-location");

  let selectedType = "4-wheeler";

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabs.forEach((t) => t.classList.remove("active"));

      tab.classList.add("active");
      selectedType = tab.dataset.type;
    });
  });

  if (searchBtn) {
    searchBtn.addEventListener("click", () => {
      const pickup = pickupInput.value.trim();
      const date = dateInput.value;
      const travelValue = travelInput.value.trim();

      if (!pickup || !date || !travelValue) {
        if (!pickup) pickupInput.focus();
        else if (!date) dateInput.focus();
        else travelInput.focus();
        return;
      }

      const travel = travelValue;

      const targetPage = "search.php";
      const queryParams = new URLSearchParams({
        type: selectedType,
        pickup,
        dropoff: travel,
        date,
        travel,
      });

      window.location.href = `${targetPage}?${queryParams.toString()}`;
    });
  }

  if (dateInput) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);
    dateInput.value = today;
  }
});
