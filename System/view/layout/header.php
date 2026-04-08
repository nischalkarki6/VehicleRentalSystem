<?php include __DIR__ . '/auth_header.php'; ?>
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-content">
        <div class="nav-logo-wrap">
          <a href="index.php" style="text-decoration: none">
            <div class="logo">CarCodemandu</div>
          </a>
        </div>
        <div class="nav-links">
          <a href="index.php" class="<?= (isset($page) && $page === 'home') ? 'active' : '' ?>">Home</a>
          <a href="bookings.php" class="<?= (isset($page) && $page === 'bookings') ? 'active' : '' ?>">Bookings</a>
          <a href="about.php" class="<?= (isset($page) && $page === 'about') ? 'active' : '' ?>">About us</a>
          <a href="contact.php" class="<?= (isset($page) && $page === 'contact') ? 'active' : '' ?>">Contact</a>
        </div>
        <div class="nav-actions">
          <a href="login.php" class="login-link">Log In</a>
          <button class="btn-primary" onclick="window.location.href = 'signup.php'">Sign up</button>
        </div>
      </div>
    </nav>