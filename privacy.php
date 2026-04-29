<?php
require_once "config/config.php";
$title = "Privacy Policy | DriveEase";
$page = "privacy";
$css = "privacy";
include "view/layout/header.php";
?>

<main class="page-container container">
      <div class="page-header">
        <h1>Privacy Policy</h1>
        <p>How we protect and handle your personal data.</p>
      </div>
      <div class="text-page-content">
        <h2>Data Collection</h2>
        <p>
          We collect essential information to facilitate your car rental booking
          including personal ID, drivers license, and payment processing
          details.
        </p>
        <h2>Security</h2>
        <p>
          All data is encrypted globally. DriveEase guarantees that no 3rd
          parties will have authorized access to read your core identifiers.
        </p>
      </div>
    </main>

<?php include "view/layout/footer.php"; ?>
