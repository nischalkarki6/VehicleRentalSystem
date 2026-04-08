<?php
$title = 'Login | CarCodemandu';
$css = 'login';
$js = 'login';
include 'view/layout/auth_header.php';
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card">
      <div class="auth-logo">CarCodemandu</div>
      <div class="auth-subtitle">Premium Vehicle Management</div>

      <div class="auth-tabs">
        <a href="login.php" class="auth-tab active">Login</a>
        <a href="signup.php" class="auth-tab">Sign Up</a>
      </div>

      <form action="#" method="POST">
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrapper">
            <input type="email" placeholder="name@domain.com" />
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 0">
          <label>Password</label>
          <div class="input-wrapper" style="position: relative">
            <input type="password" id="password" placeholder="••••••••" />
            <span
              class="material-symbols-outlined"
              id="togglePassword"
              style="
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #999;
                font-size: 1.2rem;
                cursor: pointer;
              "
              >visibility_off</span
            >
          </div>
          <a href="reset.php" class="forgot-pass">Forgot Password?</a>
        </div>

        <button class="auth-btn">
          <span>Sign In</span>
          <span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </form>

      <div class="auth-footer">
        <div class="auth-footer-text">
          Access your exclusive garage and inventory analytics.
        </div>
        <div class="auth-icons">
          <span class="material-symbols-outlined">security</span>
          <span class="material-symbols-outlined">lock</span>
        </div>
      </div>
    </div>

    
<?php include 'view/layout/auth_footer.php'; ?>
