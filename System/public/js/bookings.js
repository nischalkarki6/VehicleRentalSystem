document.addEventListener("DOMContentLoaded", () => {
  // 1. Reading URL parameters from index.php
  const params = new URLSearchParams(window.location.search);
  const pickup = params.get("pickup");
  const date = params.get("date");
  const travel = params.get("travel");

  if (pickup) {
    const displayLocation = travel || pickup;
    document.getElementById("display-location").textContent = displayLocation;
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
  const ccButtons = document.querySelectorAll("#cc-filters .cc-pill");
  const priceRange = document.getElementById("price-range");
  const priceLimitText = document.getElementById("price-limit");
  const resetButton = document.getElementById("reset-filters");
  const showingCountText = document.getElementById("showing-count");

  let activeFilters = {
    types: [],
    cc: "Any",
    maxPrice: 5000,
  };

  const updateActiveTypes = () => {
    activeFilters.types = Array.from(typeCheckboxes)
      .filter((cb) => cb.checked)
      .map((cb) => cb.value);
  };

  const checkCCRange = (cc, range) => {
    if (range === "Any") return true;
    const ccVal = parseInt(cc);
    if (range === "125-150") return ccVal >= 125 && ccVal <= 150;
    if (range === "160-250") return ccVal >= 160 && ccVal <= 250;
    if (range === "250+") return ccVal > 250;
    return true;
  };

  const filterVehicles = () => {
    let visibleCount = 0;
    cards.forEach((card) => {
      const type = card.dataset.type;
      const cc = card.dataset.cc;
      const price = parseInt(card.dataset.price);

      const matchesType =
        activeFilters.types.length === 0 || activeFilters.types.includes(type);
      const matchesCC = checkCCRange(cc, activeFilters.cc);
      const matchesPrice = price <= activeFilters.maxPrice;

      if (matchesType && matchesCC && matchesPrice) {
        card.style.display = "block";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (showingCountText) {
      showingCountText.textContent = `Showing ${visibleCount} of ${cards.length} available two wheelers`;
    }
  };

  // Event Listeners
  typeCheckboxes.forEach((cb) => {
    cb.addEventListener("change", () => {
      updateActiveTypes();
      filterVehicles();
    });
  });

  ccButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      ccButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      activeFilters.cc = btn.dataset.value;
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
      typeCheckboxes.forEach((cb) => (cb.checked = true));
      ccButtons.forEach((b) => b.classList.remove("active"));
      ccButtons[0].classList.add("active"); // "Any"
      activeFilters.cc = "Any";

      if (priceRange) {
        priceRange.value = 5000;
        priceLimitText.textContent = "NPR 5,000";
        activeFilters.maxPrice = 5000;
      }

      updateActiveTypes();
      filterVehicles();
    });
  }

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

  updateActiveTypes();
  filterVehicles();
});
