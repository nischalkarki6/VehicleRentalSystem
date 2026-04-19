<?php
require_once "config/config.php";
require_once "controllers/VehicleController.php";

$vehCtrl = new VehicleController($pdo);

// Optional filtering
$category = $_GET["category"] ?? null;
if ($category) {
    $vehicles = $vehCtrl->getByCategory($category);
} else {
    $vehicles = $vehCtrl->getAvailableVehicles();
}

$title = "Fleet | DriveEase";
$page = "fleet";
$css = "fleet";
include "view/layout/header.php";
?>

<main class="page-container container">
  <div class="page-header">
    <h1>Our Fleet</h1>
    <p>Premium vehicles for your premium journey across Nepal.</p>
    
    <div class="filter-tabs">
      <a href="fleet.php" class="filter-btn <?= !$category
          ? "active"
          : "" ?>">All</a>
      <a href="fleet.php?category=Car" class="filter-btn <?= $category === "Car"
          ? "active"
          : "" ?>">Cars</a>
      <a href="fleet.php?category=Bike" class="filter-btn <?= $category ===
      "Bike"
          ? "active"
          : "" ?>">Bikes</a>
    </div>
  </div>

  <div class="fleet-grid">
    <?php if (empty($vehicles)): ?>
      <div class="empty-state">
        <p>No vehicles found in this category.</p>
      </div>
    <?php else: ?>
      <?php foreach ($vehicles as $v): ?>
        <div class="fleet-card">
          <div class="fleet-img-wrapper">
            <?php if (!empty($v["ImageURL"])): ?>
              <img src="<?= htmlspecialchars(
                  $v["ImageURL"],
              ) ?>" class="fleet-img" alt="<?= htmlspecialchars(
    $v["Name"],
) ?>" />
            <?php else: ?>
              <div class="fleet-img-placeholder">
                <span class="material-symbols-outlined">directions_car</span>
              </div>
            <?php endif; ?>
            <?php if (!empty($v["Type"])): ?>
              <div class="fleet-badge"><?= htmlspecialchars($v["Type"]) ?></div>
            <?php endif; ?>
          </div>
          
          <div class="fleet-details">
            <h3><?= htmlspecialchars($v["Name"]) ?></h3>
            <div class="fleet-specs">
              <span><span class="material-symbols-outlined">settings</span> <?= $v[
                  "Transmission"
              ] ?></span>
              <span><span class="material-symbols-outlined">local_gas_station</span> <?= $v[
                  "FuelType"
              ] ?></span>
              <?php if ($v["Category"] === "Bike"): ?>
                <span><span class="material-symbols-outlined">bolt</span> <?= $v[
                    "EngineCC"
                ] ?>cc</span>
              <?php endif; ?>
            </div>
            
            <div class="fleet-footer">
              <div class="fleet-price">
                <span class="price-val">NPR <?= number_format(
                    $v["DailyRate"],
                    0,
                ) ?></span>
                <span class="price-unit">/ day</span>
              </div>
              <?php if (isset($_SESSION["user_id"])): ?>
                <button class="btn-primary" onclick="window.location.href='bookings.php?vehicle_id=<?= $v[
                    "VehicleID"
                ] ?>'">Rent Now</button>
              <?php else: ?>
                <a href="login.php" class="btn-primary" style="text-decoration:none;text-align:center">Login to Rent</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
