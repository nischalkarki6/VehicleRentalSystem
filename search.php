<?php
require_once "config/config.php";
require_once "controllers/VehicleController.php";

$vehCtrl = new VehicleController($pdo);

// Get search params
$category =
    isset($_GET["type"]) && $_GET["type"] === "2-wheeler" ? "Bike" : "Car";
$pickup = $_GET["pickup"] ?? "";
$date = $_GET["date"] ?? "";

// Search based on category
$vehicles = $vehCtrl->getByCategory($category);

$title = "Search Results | DriveEase";
$page = "search";
$css = "fleet"; // Reusing fleet styles for consistency
include "view/layout/header.php";
?>

<main class="page-container container">
  <div class="page-header">
    <h1>Search Results</h1>
    <p>Available <?= strtolower(
        $category,
    ) ?>s for your trip starting <?= htmlspecialchars(
    $date,
) ?> from <?= htmlspecialchars($pickup) ?>.</p>
  </div>

  <div class="fleet-grid">
    <?php if (empty($vehicles)): ?>
      <div class="empty-state">
        <p>No <?= strtolower(
            $category,
        ) ?>s available for the selected criteria.</p>
        <a href="fleet.php" class="btn-primary mt-1 d-inline-block">View All Fleet</a>
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
                ] ?>&start_date=<?= urlencode($date) ?>&pickup=<?= urlencode(
    $pickup,
) ?>'">Book Now</button>
              <?php else: ?>
                <a href="login.php" class="btn-primary" style="text-decoration:none;text-align:center">Login to Book</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
