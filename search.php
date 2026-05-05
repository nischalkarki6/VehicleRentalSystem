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
$css = "search"; // Using search styles for this page
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

  <div class="v-grid">
    <?php if (empty($vehicles)): ?>
      <div class="text-center" style="grid-column: 1 / -1; padding: 4rem;">
        <p class="text-muted mb-15">No <?= strtolower($category) ?>s available for the selected criteria.</p>
        <a href="fleet.php" class="btn-primary">View All Fleet</a>
      </div>
    <?php else: ?>
      <?php foreach ($vehicles as $v): ?>
        <div class="v-card">
          <div class="v-card-img" style="background-image: url('<?= htmlspecialchars($v["ImageURL"] ?: "https://via.placeholder.com/400x240?text=No+Image") ?>');"></div>
          <div class="v-card-body">
            <div class="v-card-header">
              <div class="v-card-tags">
                <span class="v-card-tag available">Available</span>
                <span class="v-card-tag type"><?= htmlspecialchars($v["Type"] ?: $v["Category"]) ?></span>
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
            <?php 
              $bookUrl = "bookings.php?vehicle_id=" . $v["VehicleID"] . 
                         "&start_date=" . urlencode($date) . 
                         "&pickup=" . urlencode($pickup) . 
                         "&dropoff=" . urlencode($_GET['dropoff'] ?? $pickup) . 
                         "&travel=" . urlencode($_GET['travel'] ?? $pickup);
            ?>
            <?php if (isset($_SESSION["user_id"])): ?>
              <button class="btn-book" onclick="window.location.href='<?= $bookUrl ?>'">Book Now</button>
            <?php else: ?>
              <a href="login.php?redirect=<?= urlencode($bookUrl) ?>" class="btn-book">Login to Book</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
