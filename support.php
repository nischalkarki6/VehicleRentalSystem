<?php
require_once "config/config.php";
$title = "Support | DriveEase";
$page = "support";
$css = "support";
include "view/layout/header.php";
?>

<main class="page-container container">
      <div class="page-header">
        <h1>Help & Support</h1>
        <p>Find answers to common questions.</p>
      </div>
      <div class="text-page-content">
        <h2>FAQ</h2>
        <p>
          <b>Q: Do I need insurance?</b><br />A: All our rentals come with
          standard comprehensive insurance.
        </p>
        <p>
          <b>Q: Can I pick up in Kathmandu and drop off in Pokhara?</b><br />A:
          Yes, one-way trips are fully supported using our easy UI tool.
        </p>
        <br />
        <p>
          Need more help?
          <a href="contact.php" class="font-bold"
            >Contact our team.</a
          >
        </p>
      </div>
    </main>

<?php include "view/layout/footer.php"; ?>
