<?php
require_once "config/config.php";
$title = "Terms | DriveEase";
$page = "terms";
$css = "terms";
include "view/layout/header.php";
?>

<main class="page-container container">
      <div class="page-header">
        <h1>Terms of Service</h1>
        <p>Basic rental agreements and policies.</p>
      </div>
      <div class="text-page-content">
        <h2>Rental Policies</h2>
        <p>
          Drivers must be above 21 years of age and hold a registered driver's
          license. All vehicles must be returned to the drop-off location with a
          matching fuel level.
        </p>
        <h2>Cancellations</h2>
        <p>
          Bookings can be canceled up to 48 hours in advance for a full refund.
          Last-minute cancelations are subject to a nominal fee.
        </p>
      </div>
    </main>

<?php include "view/layout/footer.php"; ?>
