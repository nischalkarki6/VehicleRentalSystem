<?php
require_once "config/config.php";
$title = "About | DriveEase";
$page = "about";
$css = "about";
include "view/layout/header.php";
?>

<main class="page-container container">
      <div class="page-header">
        <h1>About DriveEase</h1>
        <p>
          The leading vehicle rental platform in Nepal, dedicated to seamless
          travel experiences.
        </p>
      </div>
      <div class="text-page-content">
        <h2>Our Mission</h2>
        <p>
          Founded in 2003, we set out to revolutionize mobility across standard
          and difficult terrains in Nepal. From premium SUVs to lightweight city
          2-wheelers, our fleet is rigorously maintained to international
          standards.
        </p>
        <h2>Our Values</h2>
        <p>
          Quality, safety, and customer satisfaction drive every decision we
          make. We aim to empower our customers with complete freedom on the
          road with total peace of mind.
        </p>
      </div>
    </main>

<?php include "view/layout/footer.php"; ?>
