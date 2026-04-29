<?php
require_once __DIR__ . "/../../config/config.php";
include __DIR__ . "/auth_header.php";
?>
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-content">
        <div class="nav-logo-wrap">
          <a href="index.php" class="text-decoration-none">
            <div class="logo">DriveEase</div>
          </a>
        </div>
        <div class="nav-links">
          <a href="index.php"    class="<?= isset($page) && $page === "home"
              ? "active"
              : "" ?>">Home</a>
          <a href="fleet.php" class="<?= isset($page) && $page === "fleet"
              ? "active"
              : "" ?>">Fleet</a>
          <a href="about.php"    class="<?= isset($page) && $page === "about"
              ? "active"
              : "" ?>">About us</a>
          <a href="contact.php"  class="<?= isset($page) && $page === "contact"
              ? "active"
              : "" ?>">Contact</a>
        </div>
        <div class="nav-actions">
          <?php if (isset($_SESSION["user_id"])): ?>
            <a href="dashboard.php" class="login-link d-flex align-items-center gap-04">
              <span class="material-symbols-outlined font-icon-md">person</span>
              Profile
            </a>
            <?php if ($_SESSION["role"] === "admin"): ?>
              <a href="admin.php" class="login-link d-flex align-items-center gap-04">
                <span class="material-symbols-outlined font-icon-md">admin_panel_settings</span>
                Admin
              </a>
            <?php endif; ?>
          <?php else: ?>
            <a href="login.php" class="login-link">Log In</a>
            <a href="signup.php" class="login-link">Sign up</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>