document.addEventListener("DOMContentLoaded", () => {
  const transChecks = document.querySelectorAll(".filter-trans");
  const priceRange = document.getElementById("priceRange");
  const priceLabelOut = document.getElementById("priceLabelOut");
  const cards = document.querySelectorAll(".filterable-card");

  function applyFilters() {
    const checkedTrans = Array.from(transChecks)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value);
    const minPrice = priceRange ? parseInt(priceRange.min, 10) : 5000;
    const maxPrice = priceRange ? parseInt(priceRange.value, 10) : minPrice;
    const showAllPrices = !priceRange || maxPrice === minPrice;

    if (priceLabelOut) {
      priceLabelOut.textContent = showAllPrices
        ? "Show all"
        : maxPrice >= 15000
          ? "NPR 15000+"
          : `NPR ${maxPrice}`;
    }

    cards.forEach((card) => {
      const matchesTransmission =
        transChecks.length === 0 || checkedTrans.includes(card.dataset.transmission);
      const matchesPrice =
        showAllPrices || parseInt(card.dataset.price, 10) <= maxPrice;

      card.style.display = matchesTransmission && matchesPrice ? "flex" : "none";
    });

    const visible = Array.from(cards).filter(
      (card) => card.style.display !== "none",
    ).length;
    const showingText = document.querySelector(".showing-text");

    if (showingText) {
      const activeCategory = document.querySelector(".toggle-btn.active");
      const categoryLabel = activeCategory
        ? activeCategory.textContent.trim().toLowerCase()
        : "vehicles";

      showingText.textContent = `Showing ${visible} of ${cards.length} available ${categoryLabel}`;
    }
  }

  function resetFilters() {
    transChecks.forEach((checkbox) => {
      checkbox.checked = true;
    });

    if (priceRange) {
      priceRange.value = 5000;
    }

    if (priceLabelOut) {
      priceLabelOut.textContent = "Show all";
    }

    applyFilters();
  }

  transChecks.forEach((checkbox) => {
    checkbox.addEventListener("change", applyFilters);
  });

  if (priceRange) {
    priceRange.addEventListener("input", applyFilters);
  }

  document.querySelector(".btn-reset")?.addEventListener("click", resetFilters);
  document.querySelector(".btn-contact-support")?.addEventListener("click", () => {
    window.location.href = "contact.php";
  });

  applyFilters();
});
