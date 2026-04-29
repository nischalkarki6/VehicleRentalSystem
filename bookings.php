<?php
require_once "config/config.php";
require_once "controllers/BookingController.php";
require_once "controllers/VehicleController.php";

requireLogin(); // User must be logged in to book

$bookCtrl = new BookingController($pdo);

$vehicleId = (int) ($_GET["vehicle_id"] ?? 0);
$vehicle = null;

if ($vehicleId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Vehicles WHERE VehicleID = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();
}

if (!$vehicle) {
    setFlash("error", "Please select a vehicle to book.");
    redirect("fleet.php");
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = $bookCtrl->create($_SESSION["user_id"], $_POST);
    if ($result["success"]) {
        setFlash("success", "Booking requested! Waiting for admin approval.");
        redirect("dashboard.php");
    } else {
        $errors = $result["errors"];
    }
}

$title = "Complete Booking | DriveEase";
$page = "bookings";
$css = "bookings";
$js = "bookings";
include "view/layout/header.php";
?>

<main class="page-container container">
  <div class="booking-flow">
    <div class="booking-summary-card">
      <div class="summary-header">
        <h2>Confirm Your Rental</h2>
        <p>You are booking <strong><?= htmlspecialchars(
            $vehicle["Name"],
        ) ?></strong></p>
      </div>
      
      <div class="summary-body">
        <div class="vehicle-preview">
          <?php if (!empty($vehicle["ImageURL"])): ?>
            <img src="<?= htmlspecialchars(
                $vehicle["ImageURL"],
            ) ?>" alt="<?= htmlspecialchars($vehicle["Name"]) ?>" />
          <?php else: ?>
            <div class="img-placeholder"><span class="material-symbols-outlined">directions_car</span></div>
          <?php endif; ?>
        </div>
        
        <div class="price-info">
          <div class="price-row">
            <span>Daily Rate</span>
            <span>NPR <?= number_format($vehicle["DailyRate"], 2) ?></span>
          </div>
          <div class="price-row">
            <span>Duration</span>
            <span id="duration-display">1 day</span>
          </div>
          <div class="price-row total">
            <span>Estimated Total</span>
            <span id="estimated-total">NPR <?= number_format(
                $vehicle["DailyRate"],
                2,
            ) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="booking-form-wrap">
      <form method="POST" class="auth-card w-100 no-shadow border-light">
        <input type="hidden" name="vehicle_id" value="<?= $vehicleId ?>" />

        <div class="form-row">
          <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_date" id="start_date" class="dash-input" 
                   min="<?= date("Y-m-d") ?>"
                   value="<?= htmlspecialchars(
                       $_GET["start_date"] ?? date("Y-m-d"),
                   ) ?>" required />
          </div>
          <div class="form-group">
            <label>End Date</label>
            <input type="date" name="end_date" id="end_date" class="dash-input" 
                   min="<?= date("Y-m-d", strtotime("+1 day")) ?>"
                   value="<?= htmlspecialchars(
                       $_GET["end_date"] ?? date("Y-m-d", strtotime("+1 day")),
                   ) ?>" required />
          </div>
        </div>

        <div class="form-group">
          <label>Pickup Location</label>
          <input type="text" name="pickup_loc" class="dash-input" 
                 value="<?= htmlspecialchars(
                     $_GET["pickup"] ?? "",
                 ) ?>" placeholder="e.g. Kathmandu Airport" required />
        </div>

        <div class="form-group">
          <label>Dropoff Location</label>
          <input type="text" name="dropoff_loc" class="dash-input" placeholder="e.g. Pokhara Lakeside" required />
        </div>

        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $field => $msg): ?>
            <div class="alert alert-error" style="margin-bottom:0.5rem"><?= htmlspecialchars(
                $msg,
            ) ?></div>
          <?php endforeach; ?>
        <?php endif; ?>

        <p style="font-size:0.8rem;color:#999;margin-top:1.5rem">
          <span class="material-symbols-outlined" style="font-size:0.9rem;vertical-align:middle">info</span>
          Final cost is calculated server-side based on the daily rate and rental duration.
        </p>

        <button type="submit" class="auth-btn mt-1">
          <span>Confirm Booking</span>
          <span class="material-symbols-outlined">check_circle</span>
        </button>
      </form>
    </div>
  </div>
</main>

  <input type="hidden" id="vehicle-daily-rate" value="<?= htmlspecialchars(
      $vehicle["DailyRate"],
  ) ?>" />
<?php include "view/layout/footer.php"; ?>
