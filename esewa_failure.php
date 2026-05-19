<?php
require_once "config/config.php";
require_once "models/Booking.php";

requireLogin();

$bookingModel = new Booking($pdo);

$encodedData = $_GET['data'] ?? '';

if (!empty($encodedData)) {
    $decoded = base64_decode($encodedData, true);
    if ($decoded) {
        $responseData = json_decode($decoded, true);
        if ($responseData && !empty($responseData['transaction_uuid'])) {
            $booking = $bookingModel->findByTransactionUUID($responseData['transaction_uuid']);
            if (
                $booking &&
                (int)$booking['UserID'] === (int)$_SESSION['user_id'] &&
                $booking['PaymentStatus'] === 'Unpaid'
            ) {
                // Delete the incomplete booking so it never shows in history
                $bookingModel->delete((int)$booking['RentalID']);
            }
        }
    }
}

$title = "Payment Failed | DriveEase";
$page  = "esewa";
$css   = "esewa";
include "view/layout/header.php";
?>

<main class="esewa-container">
  <div class="esewa-card">
    <div class="esewa-icon-wrap failure">
      <span class="material-symbols-outlined">cancel</span>
    </div>
    <h1>Payment Failed</h1>
    <p>Your payment could not be completed. Your booking has been removed - no charges were made. Please try again to make a new booking.</p>

    <div class="esewa-btn-group">
      <a href="fleet.php" class="esewa-btn esewa-btn-primary">
        <span class="material-symbols-outlined">directions_car</span> Book Again
      </a>
      <a href="dashboard.php" class="esewa-btn esewa-btn-secondary">
        <span class="material-symbols-outlined">dashboard</span> Dashboard
      </a>
    </div>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
