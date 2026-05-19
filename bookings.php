<?php
require_once "config/config.php";
require_once "controllers/BookingController.php";
require_once "controllers/VehicleController.php";

requireLogin(); // User must be logged in to book

$bookCtrl = new BookingController($pdo);
$isAdmin = (($_SESSION["role"] ?? "") === "admin");

$vehicleId = (int) ($_GET["vehicle_id"] ?? 0);
$vehicle = null;
$prefillStartDate = $_POST["start_date"] ?? $_GET["start_date"] ?? $_GET["date"] ?? date("Y-m-d");
$prefillEndDate = $_POST["end_date"] ?? $_GET["end_date"] ?? "";
$prefillPickup = $_POST["pickup_loc"] ?? $_GET["pickup"] ?? "";
$prefillDestination = $_POST["dropoff_loc"] ?? $_GET["travel"] ?? $_GET["destination"] ?? $_GET["dropoff"] ?? "";

$normalizeDate = static function (string $value): string {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $dateErrors = DateTimeImmutable::getLastErrors();
    $hasErrors = is_array($dateErrors) &&
        ($dateErrors["warning_count"] > 0 || $dateErrors["error_count"] > 0);

    return (!$date || $hasErrors || $date->format('Y-m-d') !== $value) ? "" : $value;
};
$prefillStartDate = $normalizeDate((string) $prefillStartDate) ?: date("Y-m-d");
$prefillEndDate = $normalizeDate((string) $prefillEndDate);
if ($prefillEndDate !== "" && $prefillEndDate <= $prefillStartDate) {
    $prefillEndDate = "";
}

if ($vehicleId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Vehicles WHERE VehicleID = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();
    
    if ($vehicle) {
        $vehModel = new Vehicle($pdo);
        $multiplier = $vehModel->calculateCategoryMultiplier($vehicle['Category']);
        if ($multiplier > 1.0) {
            $vehicle['OriginalRate'] = $vehicle['DailyRate'];
            $vehicle['DailyRate'] = round((float)$vehicle['DailyRate'] * $multiplier);
            $vehicle['IsDynamicPrice'] = true;
        }
    }
}

if (!$vehicle) {
    setFlash("error", "Please select a vehicle to book.");
    redirect("fleet.php");
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($isAdmin) {
        $errors["form"] = "Admins can view fleet details only. Booking is available for customer accounts.";
    } elseif (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $errors["form"] = "Invalid request. Please try again.";
    } else {
        $result = $bookCtrl->create($_SESSION["user_id"], $_POST);
        if ($result["success"]) {
            setFlash("success", "Booking requested! Redirecting to payment...");
            redirect("esewa_pay.php");
        } else {
            $errors = $result["errors"];
        }
    }
}

$title = htmlspecialchars($vehicle["Name"]) . " | DriveEase";
$page = "bookings";
$css = "bookings";
$js = "bookings";
include "view/layout/header.php";
?>

<main class="vd-container">
  <!-- Hero Section -->
  <div class="vd-hero">
    <div class="vd-hero-img">
      <img src="<?= htmlspecialchars($vehicle['ImageURL'] ?: 'https://via.placeholder.com/1200x600?text=No+Image') ?>"
           alt="<?= htmlspecialchars($vehicle["Name"]) ?>">
    </div>
  </div>

  <div class="vd-content-grid container">
    <!-- Main Left Column -->
    <div class="vd-main-col">
      <div class="vd-header">
        <div class="vd-back-wrap">
          <a href="fleet.php" class="vd-back-link" data-history-back>
            <span class="material-symbols-outlined">arrow_back</span>
            BACK TO RESULTS
          </a>
        </div>
        <span class="vd-eyebrow">PREMIUM SELECTION</span>
        <h1><?= htmlspecialchars($vehicle["Name"]) ?></h1>
        <p>Experience peak performance and comfort with our meticulously maintained <?= htmlspecialchars(strtolower($vehicle["Category"])) ?>.</p>
      </div>

      <div class="vd-section">
        <h2>Technical Specifications</h2>
        <div class="specs-grid">
          <div class="spec-box"><span class="material-symbols-outlined">category</span> <small>CATEGORY</small> <strong><?= htmlspecialchars($vehicle["Category"]) ?></strong></div>
          <?php if (!empty($vehicle["Type"])): ?>
          <div class="spec-box"><span class="material-symbols-outlined">style</span> <small>TYPE</small> <strong><?= htmlspecialchars($vehicle["Type"]) ?></strong></div>
          <?php endif; ?>
          <div class="spec-box"><span class="material-symbols-outlined">settings</span> <small>TRANSMISSION</small> <strong><?= htmlspecialchars($vehicle["Transmission"]) ?></strong></div>
        </div>
      </div>

      <div class="vd-section">
        <h2>The Driving Experience</h2>
        <p class="text-muted">The <?= htmlspecialchars($vehicle["Name"]) ?> is engineered to deliver a seamless blend of performance and reliability. Every component is rigorously tested to ensure maximum safety and comfort during your journey. Whether you're navigating urban environments or exploring rural landscapes, this <?= htmlspecialchars(strtolower($vehicle["Category"])) ?> provides the agility and power you need for a memorable travel experience.</p>
      </div>

      <div class="vd-section safety-box">
        <h2>Safety & Reliability</h2>
        <div class="safety-item">
          <span class="material-symbols-outlined">shield</span>
          <div>
            <strong>Full Insurance Coverage</strong>
            <small>Comprehensive protection for total peace of mind.</small>
          </div>
        </div>
        <div class="safety-item">
          <span class="material-symbols-outlined">verified</span>
          <div>
            <strong>Meticulously Inspected</strong>
            <small>Every vehicle undergoes a 50-point safety check before rental.</small>
          </div>
        </div>
      </div>

      <!-- Similar Premium Fleet (Dynamic) -->
      <?php 
        $stmt = $pdo->prepare("SELECT * FROM Vehicles WHERE Category = ? AND VehicleID != ? LIMIT 4");
        $stmt->execute([$vehicle["Category"], $vehicle["VehicleID"]]);
        $similar = $stmt->fetchAll();
      ?>
      <?php if (!empty($similar)): ?>
      <div class="vd-similar-fleet">
        <span class="vd-eyebrow vd-similar-title">SIMILAR <?= strtoupper($vehicle["Category"]) ?>S</span>
        <div class="similar-icons">
          <?php foreach ($similar as $sv): ?>
            <a href="bookings.php?vehicle_id=<?= $sv["VehicleID"] ?>" class="sim-icon text-decoration-none">
              <span class="material-symbols-outlined"><?= $vehicle["Category"] === "Car" ? "directions_car" : "two_wheeler" ?></span>
              <small><?= htmlspecialchars($sv["Name"]) ?></small>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Booking Form Sidebar -->
    <div class="vd-side-col">
      <div class="booking-widget">
        <div class="widget-header">
          <div>
            <span class="widget-eyebrow">DAILY RATE</span>
            <div class="widget-price">Rs. <span id="base-price"><?= number_format($vehicle['DailyRate'], 0) ?></span></div>
            <?php if (!empty($vehicle['IsDynamicPrice'])): ?>
              <div class="dynamic-price-note">Includes Surge Pricing</div>
            <?php endif; ?>
          </div>
          <?php if (!empty($vehicle['IsDynamicPrice'])): ?>
            <span class="badge-high-demand">DYNAMIC</span>
          <?php endif; ?>
        </div>
        
        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $field => $msg): ?>
            <div class="booking-error">
              <?= htmlspecialchars($msg) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
          <div class="booking-error">
            Admin view only. Customer booking and payment controls are disabled.
          </div>
          <div class="receipt">
            <div class="receipt-row">
              <span>Status</span>
              <span>View only</span>
            </div>
            <div class="receipt-row">
              <span>Category</span>
              <span><?= htmlspecialchars($vehicle["Category"]) ?></span>
            </div>
            <div class="receipt-row total">
              <span>Daily Rate</span>
              <span>Rs. <?= number_format($vehicle['DailyRate'], 0) ?></span>
            </div>
          </div>
        <?php else: ?>
        <form method="POST" class="widget-form" id="bookingForm">
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
          <input type="hidden" name="vehicle_id" value="<?= $vehicleId ?>" />
          <input type="hidden" id="daily_rate" value="<?= $vehicle['DailyRate'] ?>" />
          
          <div class="form-group-vd">
            <label>PICKUP LOCATION</label>
            <div class="input-with-icon">
              <span class="material-symbols-outlined">location_on</span>
              <input type="text" name="pickup_loc" value="<?= htmlspecialchars($prefillPickup) ?>" required>
            </div>
          </div>
          <div class="form-group-vd">
            <label>DESTINATION</label>
            <div class="input-with-icon">
              <span class="material-symbols-outlined">flag</span>
              <input type="text" name="dropoff_loc" value="<?= htmlspecialchars($prefillDestination) ?>" required>
            </div>
          </div>

          <div class="vd-row">
            <div class="form-group-vd">
              <label>START DATE</label>
              <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($prefillStartDate) ?>" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group-vd">
              <label>END DATE</label>
              <?php 
                $startDateStr = $prefillStartDate;
                $defaultEndDate = $prefillEndDate ?: date('Y-m-d', strtotime($startDateStr . ' + 3 days'));
                $minEndDate = date('Y-m-d', strtotime($startDateStr . ' + 1 day'));
              ?>
              <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($defaultEndDate) ?>" min="<?= $minEndDate ?>" required>
            </div>
          </div>

          <?php 
            $startDt = new DateTime($startDateStr);
            $endDt = new DateTime($defaultEndDate);
            $diffDays = max(1, (int)$endDt->diff($startDt)->days);
            $initialRentalCost = $diffDays * $vehicle['DailyRate'];
            $initialInsuranceFee = $vehicle['DailyRate'] > 4500 ? 3300 : 0;
            $initialTotalCost = $initialRentalCost + $initialInsuranceFee;
          ?>
          <div class="receipt">
            <div class="receipt-row">
              <span id="rental-days-label">Rental (<?= $diffDays ?> day<?= $diffDays > 1 ? 's' : '' ?>)</span>
              <span id="rental-cost-val">Rs. <?= number_format($initialRentalCost, 0) ?></span>
            </div>
            <?php if ($initialInsuranceFee > 0): ?>
            <div class="receipt-row" id="insurance-row">
              <span>Premium Insurance</span>
              <span>Rs. <?= number_format($initialInsuranceFee, 0) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row total">
              <span>Total</span>
              <span id="total-cost-val">Rs. <?= number_format($initialTotalCost, 0) ?></span>
            </div>
          </div>

          <button type="button" class="btn-confirm-booking" id="openTncModal">
            <span class="material-symbols-outlined">payments</span> Proceed to Payment
          </button>
          <div class="no-credit-card">
            <span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle">lock</span>
            SECURE PAYMENT VIA <strong>eSewa</strong>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<!-- Terms & Conditions Modal -->
<?php if (!$isAdmin): ?>
<div id="tncModal" class="tnc-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="tncModalTitle">
  <div class="tnc-modal">
    <div class="tnc-modal-header">
      <div class="tnc-header-icon">
        <span class="material-symbols-outlined">gavel</span>
      </div>
      <div class="tnc-header-text">
        <h2 id="tncModalTitle">Terms &amp; Conditions</h2>
        <p>Please read and accept our rental agreement before confirming.</p>
      </div>
      <button class="tnc-close-btn" id="closeTncModal" aria-label="Close">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <div class="tnc-body">
      <div class="tnc-scroll">
        <h3>1. Eligibility</h3>
        <p>All renters must be at least <strong>21 years of age</strong> and hold a valid, government-issued driver&#39;s license. International renters must present an International Driving Permit (IDP) alongside their national license.</p>

        <h3>2. Vehicle Use</h3>
        <p>Vehicles must only be operated on paved or approved roads within Nepal. Off-road use, racing, or sub-letting the vehicle to a third party is strictly prohibited and will void all insurance coverage.</p>

        <h3>3. Fuel Policy</h3>
        <p>Vehicles are provided with a full tank and must be returned with a matching fuel level. Failure to do so will incur a fuel surcharge at prevailing market rates plus a service fee.</p>

        <h3>4. Return Policy</h3>
        <p>All vehicles must be returned to the <strong>original pickup location</strong> by the agreed end date and time. Late returns are subject to a penalty of <strong>1.5&times; the daily rate</strong> per additional day.</p>

        <h3>5. Cancellations &amp; Refunds</h3>
        <p>Bookings cancelled more than <strong>48 hours</strong> before the start date are eligible for a full refund. Cancellations within 48 hours are subject to a nominal cancellation fee equal to one day&#39;s rental charge.</p>

        <h3>6. Damage &amp; Liability</h3>
        <p>The renter is responsible for any damage to the vehicle during the rental period. DriveEase&#39;s comprehensive insurance covers third-party liability, but does not cover damage caused by reckless driving, DUI, or violation of these terms.</p>

        <h3>7. Insurance</h3>
        <p>Premium insurance (Rs. 3,300) applies automatically for vehicles with a daily rate above Rs. 4,500. This covers comprehensive protection for total peace of mind during your journey.</p>

        <h3>8. Privacy</h3>
        <p>Your personal data collected during booking is used solely to manage your reservation and will never be sold or shared with third parties without your explicit consent, in accordance with applicable privacy laws.</p>
      </div>

      <div class="tnc-agreement">
        <label class="tnc-checkbox-label" for="agreeCheckbox">
          <span class="tnc-checkmark" id="tncCheckmark">
            <span class="material-symbols-outlined">check</span>
          </span>
          <input type="checkbox" id="agreeCheckbox">
          I have read and agree to the DriveEase <a href="terms.php" target="_blank" class="tnc-link">Terms &amp; Conditions</a>
        </label>
        <p id="tncError" class="tnc-error" aria-live="polite"></p>
      </div>
    </div>

    <div class="tnc-footer">
      <button type="button" class="tnc-btn-cancel" id="disagreeTnc">
        <span class="material-symbols-outlined">thumb_down</span>
        Disagree
      </button>
      <button type="button" class="tnc-btn-confirm" id="agreeTnc">
        <span class="material-symbols-outlined">thumb_up</span>
        Agree &amp; Confirm Booking
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include "view/layout/footer.php"; ?>
