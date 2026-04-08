<?php
$title = 'Sign Up | CarCodemandu';
$css = 'signup';
$js = 'signup';
include 'view/layout/auth_header.php';
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card">
      <a href="index.php" class="close-btn"
        ><span class="material-symbols-outlined">close</span></a
      >
      <div class="auth-logo">CarCodemandu</div>
      <h1 class="auth-title">Create an account</h1>
      <p class="auth-subtitle">
        Start your journey with premium access today and experience
        architectural mobility.
      </p>

      <div class="auth-tabs">
        <a href="signup.php" class="auth-tab active">Sign Up</a>
        <a href="login.php" class="auth-tab">Login</a>
      </div>

      <form action="#" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Full Name</label>
            <div class="input-wrapper">
              <input type="text" placeholder="Enter name" />
            </div>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <div class="input-wrapper">
              <input type="text" placeholder="Enter number" />
            </div>
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 1.5rem">
          <label>Email Address</label>
          <div class="input-wrapper">
            <input type="email" placeholder="Enter email" />
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 2rem">
          <label>Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" placeholder="Enter password" />
            <span
              class="material-symbols-outlined input-icon-right"
              id="togglePassword"
              style="cursor: pointer"
              >visibility_off</span
            >
          </div>
        </div>

        <button class="auth-btn">
          <span>Sign Up</span>
          <span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </form>

      <p class="auth-footer-text">
        By continuing, you agree to CarCodemandu's
        <a href="terms.php">Terms of Service</a> and
        <a href="privacy.php">Privacy Policy</a>.
      </p>
    </div>

    
<?php include 'view/layout/auth_footer.php'; ?>
