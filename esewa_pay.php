<?php
require_once "config/config.php";
require_once "models/Booking.php";
require_once "helpers/EsewaHelper.php";

requireLogin();

$rentalId = $_SESSION['esewa_rental_id'] ?? null;
unset($_SESSION['esewa_rental_id']);

if (!$rentalId) {
    setFlash("error", "Invalid payment session.");
    redirect("dashboard.php");
}

$bookingModel = new Booking($pdo);
$booking = $bookingModel->findById((int) $rentalId);

if (
    !$booking ||
    (int) $booking['UserID'] !== (int) $_SESSION['user_id'] ||
    $booking['PaymentStatus'] !== 'Unpaid'
) {
    setFlash("error", "Invalid or already processed booking.");
    redirect("dashboard.php");
}

$esewa = new EsewaHelper();

$totalAmount     = (float) $booking['TotalCost'];
$transactionUuid = $booking['TransactionUUID'];
$successUrl      = buildAppUrl('esewa_success.php');
$failureUrl      = buildAppUrl('esewa_failure.php');

$paymentData = $esewa->buildPaymentData($totalAmount, $transactionUuid, $successUrl, $failureUrl);
$paymentUrl  = $esewa->getPaymentUrl();

$title = "Processing Payment | DriveEase";
$page  = "esewa";
$css   = "esewa";
include "view/layout/header.php";
?>

<main class="esewa-container">
  <div class="esewa-card">
    <div class="esewa-icon-wrap loading">
      <span class="material-symbols-outlined">account_balance_wallet</span>
    </div>
    <h1>Redirecting to <span class="esewa-brand-accent">eSewa</span></h1>
    <p>You're being securely redirected to eSewa to complete your payment. Please do not close this window.</p>

    <div class="esewa-details">
      <div class="esewa-detail-row">
        <span class="label">Booking ID</span>
        <span class="value">#<?= $booking['RentalID'] ?></span>
      </div>
      <div class="esewa-detail-row">
        <span class="label">Transaction ID</span>
        <span class="value"><?= htmlspecialchars($transactionUuid) ?></span>
      </div>
      <div class="esewa-detail-row total">
        <span class="label">Total Amount</span>
        <span class="value">Rs. <?= number_format($totalAmount, 0) ?></span>
      </div>
    </div>

    <div class="esewa-progress-bar">
      <div class="esewa-progress-fill"></div>
    </div>
    <p class="esewa-redirect-note">Secure payment via eSewa ePay</p>

    <form id="esewaForm" action="<?= htmlspecialchars($paymentUrl) ?>" method="POST" style="display:none">
      <?php foreach ($paymentData as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
      <?php endforeach; ?>
    </form>

    <noscript>
      <p style="color:#b91c1c;margin-top:1rem;font-weight:600">JavaScript is required. Please click the button below:</p>
      <form action="<?= htmlspecialchars($paymentUrl) ?>" method="POST">
        <?php foreach ($paymentData as $name => $value): ?>
          <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endforeach; ?>
        <button type="submit" class="esewa-btn esewa-btn-primary" style="margin-top:1rem;width:100%">
          <span class="material-symbols-outlined">payments</span> Pay with eSewa
        </button>
      </form>
    </noscript>
  </div>
</main>

<script>
  window.onload = function() {
    const form = document.getElementById("esewaForm");
    if (form) {
      setTimeout(function() {
        form.submit();
      }, 800); // Reduced delay for better UX and state consistency
    }
  };
</script>

<?php include "view/layout/footer.php"; ?>
