<?php
require_once "config/config.php";
require_once "models/Booking.php";
require_once "helpers/EsewaHelper.php";

requireLogin();

$esewa        = new EsewaHelper();
$bookingModel = new Booking($pdo);

$encodedData = $_GET['data'] ?? '';
$errorMsg    = '';
$booking     = null;
$refId       = '';

if (empty($encodedData)) {
    setFlash("error", "No payment response received.");
    redirect("dashboard.php");
}

$decoded = base64_decode($encodedData, true);
if ($decoded === false) {
    setFlash("error", "Invalid payment response data.");
    redirect("dashboard.php");
}

$responseData = json_decode($decoded, true);
if (!$responseData || empty($responseData['transaction_uuid'])) {
    setFlash("error", "Malformed payment response.");
    redirect("dashboard.php");
}

$transactionUuid = $responseData['transaction_uuid'];
$status          = $responseData['status'] ?? '';
$totalAmount     = (float) ($responseData['total_amount'] ?? 0);
$transactionCode = $responseData['transaction_code'] ?? '';

$booking = $bookingModel->findByTransactionUUID($transactionUuid);

if (!$booking) {
    setFlash("error", "Booking not found for this transaction.");
    redirect("dashboard.php");
}

if ((int) $booking['UserID'] !== (int) $_SESSION['user_id']) {
    setFlash("error", "Unauthorized access.");
    redirect("dashboard.php");
}

if ($booking['PaymentStatus'] === 'Paid') {
    setFlash("success", "This payment was already confirmed.");
    redirect("dashboard.php");
}

$signatureValid = $esewa->verifyResponseSignature($responseData);

if (!$signatureValid) {
    error_log("[eSewa] Signature mismatch for UUID: {$transactionUuid}");
    $bookingModel->updatePaymentInfo((int) $booking['RentalID'], 'Failed', null);
    setFlash("error", "Payment verification failed. Potential fraud detected.");
    redirect("dashboard.php");
}

if ($status !== 'COMPLETE') {
    $bookingModel->updatePaymentInfo((int) $booking['RentalID'], 'Failed', null);
    setFlash("error", "Payment was not completed. Status: " . htmlspecialchars($status));
    redirect("dashboard.php");
}

$statusCheck = $esewa->checkTransactionStatus($transactionUuid, $totalAmount);

if ($statusCheck && ($statusCheck['status'] ?? '') === 'COMPLETE') {
    $refId = $statusCheck['ref_id'] ?? $transactionCode;
    $bookingModel->updatePaymentInfo((int) $booking['RentalID'], 'Paid', $refId);
    $booking = $bookingModel->findById((int) $booking['RentalID']);
} else {
    $refId = $transactionCode;
    $bookingModel->updatePaymentInfo((int) $booking['RentalID'], 'Paid', $refId);
    $booking = $bookingModel->findById((int) $booking['RentalID']);
    error_log("[eSewa] Status API check inconclusive for UUID: {$transactionUuid}. Accepted based on signature verification.");
}

$title = "Payment Successful | DriveEase";
$page  = "esewa";
$css   = "esewa";
include "view/layout/header.php";
?>

<main class="esewa-container">
  <div class="esewa-card">
    <div class="esewa-icon-wrap success">
      <span class="material-symbols-outlined">check_circle</span>
    </div>
    <h1>Payment Successful!</h1>
    <p>Your payment has been verified and your booking is now confirmed. You will receive a confirmation shortly.</p>

    <div class="esewa-details">
      <div class="esewa-detail-row">
        <span class="label">Booking ID</span>
        <span class="value">#<?= $booking['RentalID'] ?></span>
      </div>
      <div class="esewa-detail-row">
        <span class="label">Transaction ID</span>
        <span class="value"><?= htmlspecialchars($transactionUuid) ?></span>
      </div>
      <?php if ($refId): ?>
      <div class="esewa-detail-row">
        <span class="label">eSewa Ref</span>
        <span class="value"><?= htmlspecialchars($refId) ?></span>
      </div>
      <?php endif; ?>
      <div class="esewa-detail-row total">
        <span class="label">Amount Paid</span>
        <span class="value">Rs. <?= number_format((float)$booking['TotalCost'], 0) ?></span>
      </div>
    </div>

    <span class="esewa-ref-badge">
      <span class="material-symbols-outlined">verified</span>
      Paid via <span class="esewa-brand-accent">eSewa</span>
    </span>

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
