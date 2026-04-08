document.addEventListener("DOMContentLoaded", () => {
  // 1. Reading URL parameters from index.php
  const params = new URLSearchParams(window.location.search);
  const pickup = params.get("pickup");
  const date = params.get("date");
  const travel = params.get("travel");

  if (pickup) {
    document.getElementById("display-location").textContent = travel || pickup;
  }
  if (date) {
    const dateObj = new Date(date);
    const options = { year: "numeric", month: "long", day: "numeric" };
    document.getElementById("display-date").textContent =
      dateObj.toLocaleDateString("en-US", options);
  }

  // 2. Filter Functionality
  const cards = document.querySelectorAll(".vehicle-card");
  const typeCheckboxes = document.querySelectorAll(
    '#type-filters input[type="checkbox"]',
  );
  const transmissionButtons = document.querySelectorAll(
    "#transmission-filters .cc-pill",
  );
  const fuelButtons = document.querySelectorAll("#fuel-filters .cc-pill");
  const priceRange = document.getElementById("price-range");
  const priceLimitText = document.getElementById("price-limit");
  const resetButton = document.getElementById("reset-filters");
  const showingCountText = document.getElementById("showing-count");

  let activeFilters = {
    types: [],
    transmission: "Any",
    fuel: "Any",
    maxPrice: 15000,
  };

  // Initialize types
  const updateActiveTypes = () => {
    activeFilters.types = Array.from(typeCheckboxes)
      .filter((cb) => cb.checked)
      .map((cb) => cb.value);
  };

  const filterVehicles = () => {
    let visibleCount = 0;
    cards.forEach((card) => {
      const type = card.dataset.type;
      const trans = card.dataset.transmission;
      const fuel = card.dataset.fuel;
      const price = parseInt(card.dataset.price);

      const matchesType =
        activeFilters.types.length === 0 || activeFilters.types.includes(type);
      const matchesTrans =
        activeFilters.transmission === "Any" ||
        activeFilters.transmission === trans;
      const matchesFuel =
        activeFilters.fuel === "Any" || activeFilters.fuel === fuel;
      const matchesPrice = price <= activeFilters.maxPrice;

      if (matchesType && matchesTrans && matchesFuel && matchesPrice) {
        card.style.display = "block";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (showingCountText) {
      showingCountText.textContent = `Showing ${visibleCount} of ${cards.length} available four wheelers`;
    }
  };

  // Event Listeners
  typeCheckboxes.forEach((cb) => {
    cb.addEventListener("change", () => {
      updateActiveTypes();
      filterVehicles();
    });
  });

  transmissionButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      transmissionButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      activeFilters.transmission = btn.dataset.value;
      filterVehicles();
    });
  });

  fuelButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      fuelButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      activeFilters.fuel = btn.dataset.value;
      filterVehicles();
    });
  });

  if (priceRange) {
    priceRange.addEventListener("input", (e) => {
      const val = e.target.value;
      priceLimitText.textContent = `NPR ${parseInt(val).toLocaleString()}`;
      activeFilters.maxPrice = parseInt(val);
      filterVehicles();
    });
  }

  if (resetButton) {
    resetButton.addEventListener("click", () => {
      // Reset types
      typeCheckboxes.forEach((cb) => (cb.checked = true));

      // Reset transmission
      transmissionButtons.forEach((b) => b.classList.remove("active"));
      const anyTrans = document.querySelector(
        '#transmission-filters .cc-pill[data-value="Any"]',
      );
      if (anyTrans) anyTrans.classList.add("active");
      activeFilters.transmission = "Any";

      // Reset fuel
      fuelButtons.forEach((b) => b.classList.remove("active"));
      const anyFuel = document.querySelector(
        '#fuel-filters .cc-pill[data-value="Any"]',
      );
      if (anyFuel) anyFuel.classList.add("active");
      activeFilters.fuel = "Any";

      // Reset price
      if (priceRange) {
        priceRange.value = 15000;
        priceLimitText.textContent = "NPR 15,000";
        activeFilters.maxPrice = 15000;
      }

      updateActiveTypes();
      filterVehicles();
    });
  }

  // Initial update
  updateActiveTypes();

  const activeTrans = document.querySelector(
    "#transmission-filters .cc-pill.active",
  );
  if (activeTrans) activeFilters.transmission = activeTrans.dataset.value;

  const activeFuel = document.querySelector("#fuel-filters .cc-pill.active");
  if (activeFuel) activeFilters.fuel = activeFuel.dataset.value;

  // Book Now Buttons Functionality
  const bookButtons = document.querySelectorAll(".btn-book");
  bookButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const card = e.target.closest(".vehicle-card");
      const vehicleName = card.querySelector("h3").textContent;
      alert(
        `Redirecting to booking portal for: ${vehicleName}\nPlease login to continue.`,
      );
      window.location.href = "login.php";
    });
  });

  // Load More Button Functionality
  const loadMoreBtn = document.querySelector(".btn-load-more");
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", () => {
      alert("Loading more vehicles from the database...");
      loadMoreBtn.innerHTML =
        'NO MORE VEHICLES <span class="material-symbols-outlined">block</span>';
      loadMoreBtn.style.opacity = "0.5";
      loadMoreBtn.style.cursor = "not-allowed";
    });
  }

  filterVehicles();
});
