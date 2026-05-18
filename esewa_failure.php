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
                $bookingModel->updatePaymentInfo((int)$booking['RentalID'], 'Failed', null);
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
    <p>Your payment could not be completed. This may have happened because you cancelled the transaction or there was an issue with eSewa. Your booking has been saved — you can retry payment from your dashboard.</p>

    <div class="esewa-btn-group">
      <a href="dashboard.php" class="esewa-btn esewa-btn-primary">
        <span class="material-symbols-outlined">dashboard</span> Go to Dashboard
      </a>
      <a href="fleet.php" class="esewa-btn esewa-btn-secondary">
        <span class="material-symbols-outlined">directions_car</span> Browse Fleet
      </a>
    </div>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
