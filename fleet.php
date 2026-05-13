<?php
require_once "config/config.php";
require_once "controllers/VehicleController.php";

$vehCtrl = new VehicleController($pdo);

// Default to Bike if not set (as per first screenshot)
$category = $_GET["category"] ?? "Bike";
if (!in_array($category, ["Car", "Bike"])) {
    $category = "Bike";
}
$vehicles = $vehCtrl->getByCategory($category);

$title = "Available " . ($category === "Car" ? "Four Wheelers" : "Two Wheelers") . " | DriveEase";
$page = "fleet";
$css = "fleet";
$js = "fleet";
include "view/layout/header.php";
?>

<main class="search-container container">
  <!-- Header Area -->
  <div class="search-header">
    <div class="search-title-left">
      <span class="search-label">SEARCH RESULTS</span>
      <h1>Available <?= $category === "Car" ? "Four Wheelers" : "Two Wheelers" ?></h1>
      <?php 
        $displayPickup = $_GET['pickup'] ?? 'Kathmandu Valley';
        $displayDate = $_GET['date'] ?? date('F jS, Y');
      ?>
      <div class="search-params">
        <span class="material-symbols-outlined">calendar_today</span> <?= htmlspecialchars($displayDate) ?>
        <span class="dot">-</span>
        <span class="material-symbols-outlined">location_on</span> <?= htmlspecialchars($displayPickup) ?>
        <a href="index.php" class="change-link">Change</a>
      </div>
    </div>
    
    <div class="vehicle-toggle">
      <a href="fleet.php?category=Car" class="toggle-btn <?= $category === "Car" ? "active" : "" ?>">Four Wheelers</a>
      <a href="fleet.php?category=Bike" class="toggle-btn <?= $category === "Bike" ? "active" : "" ?>">Two Wheelers</a>
    </div>
  </div>

  <div class="search-layout">
    <!-- Filters Sidebar -->
    <aside class="filters-sidebar">
      <?php if ($category === "Bike"): ?>

      <?php else: ?>


        <div class="filter-group">
          <span class="filter-title">TRANSMISSION</span>
          <label class="custom-checkbox">
            <input type="checkbox" class="filter-trans" value="Automatic" checked>
            <span class="checkmark"><span class="material-symbols-outlined">check</span></span>
            Automatic
          </label>
          <label class="custom-checkbox">
            <input type="checkbox" class="filter-trans" value="Manual" checked>
            <span class="checkmark"><span class="material-symbols-outlined">check</span></span>
            Manual
          </label>
        </div>
      <?php endif; ?>

      <div class="filter-group">
        <span class="filter-title">PRICE RANGE (DAILY)</span>
        <div class="price-slider-track">
          <input type="range" id="priceRange" min="5000" max="15000" step="500" value="5000">
        </div>
        <div class="price-labels">
          <span>NPR 5000</span>
          <span id="priceLabelOut">NPR 15000+</span>
        </div>
      </div>

      <?php if ($category === "Bike"): ?>
        <button class="btn-reset">RESET FILTERS</button>
      <?php else: ?>
        <div class="need-help-card">
          <h4>Need Help?</h4>
          <p>Our mobility experts are available 24/7 for tailored bookings.</p>
          <button class="btn-contact-support">Contact Support</button>
        </div>
      <?php endif; ?>
    </aside>

    <!-- Results Grid -->
    <div>
      <div class="v-grid">
        <?php if (empty($vehicles)): ?>
          <div class="empty-results">No vehicles available in this category.</div>
        <?php else: ?>
          <?php foreach ($vehicles as $v): ?>
            <div class="v-card filterable-card" data-transmission="<?= htmlspecialchars($v["Transmission"]) ?>" data-price="<?= $v["DailyRate"] ?>">
              <div class="v-card-img">
                <img src="<?= htmlspecialchars($v["ImageURL"] ?: "https://via.placeholder.com/400x240?text=No+Image") ?>"
                     alt="<?= htmlspecialchars($v["Name"]) ?>">
              </div>
              <div class="v-card-body">
                <div class="v-card-header">
                  <div class="v-card-tags">
                    <span class="v-card-tag available">Available</span>
                    <span class="v-card-tag type"><?= htmlspecialchars($v["Type"] ?: $v["Category"]) ?></span>
                    <?php if (!empty($v["IsDynamicPrice"])): ?>
                      <span class="v-card-tag v-card-tag--surge">Surge</span>
                    <?php endif; ?>
                  </div>
                  <div class="v-card-price">
                    Rs. <?= number_format($v["DailyRate"], 0) ?>
                    <small>/ day</small>
                  </div>
                </div>
                
                <h3><?= htmlspecialchars($v["Name"]) ?></h3>
                
                <div class="v-card-specs">
                  <span><span class="material-symbols-outlined">settings</span> <?= strtoupper(htmlspecialchars($v["Transmission"])) ?></span>
                </div>
                
                <?php if (isset($_SESSION["user_id"])): ?>
                  <a href="bookings.php?vehicle_id=<?= $v["VehicleID"] ?>" class="btn-book">BOOK NOW</a>
                <?php else: ?>
                  <a href="login.php?redirect=<?= urlencode('bookings.php?vehicle_id=' . $v["VehicleID"]) ?>" class="btn-book">LOGIN TO BOOK</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <?php if (!empty($vehicles)): ?>
      <div class="load-more-container">
        <p class="showing-text">Showing all <?= count($vehicles) ?> available <?= strtolower($category === "Car" ? "four wheelers" : "two wheelers") ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
