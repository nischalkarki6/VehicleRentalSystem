<?php
require_once "config/config.php";

requireLogin();

unset($_SESSION['esewa_rental_id']);

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
    <p>Your payment could not be completed. No charges were made, and your booking is still saved as pending so you can retry payment from your order history.</p>

    <div class="esewa-btn-group">
      <a href="dashboard.php?tab=orders" class="esewa-btn esewa-btn-primary">
        <span class="material-symbols-outlined">receipt_long</span> Order History
      </a>
      <a href="fleet.php" class="esewa-btn esewa-btn-secondary">
        <span class="material-symbols-outlined">directions_car</span> Browse Fleet
      </a>
    </div>
  </div>
</main>

<?php include "view/layout/footer.php"; ?>
