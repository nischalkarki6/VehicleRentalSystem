<?php
$title = 'Reset Password | CarCodemandu';
$css = 'reset';
$js = '';
include 'view/layout/auth_header.php';
?>
  <style>
      body {
        background-color: #f7f9fb;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
      }

      .reset-header {
        padding: 2rem 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .reset-logo {
        font-size: 1.25rem;
        font-weight: 900;
        color: #111;
        letter-spacing: -0.5px;
        text-transform: uppercase;
      }
      .nav-icon {
        color: #111;
        text-decoration: none;
      }

      .main-content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
      }

      .auth-card {
        background: #fff;
        width: 100%;
        max-width: 480px;
        padding: 4rem;
        border-radius: 8px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.05);
      }
      .auth-title {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: #111;
        letter-spacing: -0.5px;
      }
      .auth-subtitle {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 2.5rem;
        max-width: 90%;
      }

      .form-group {
        margin-bottom: 1.5rem;
      }
      .form-group label {
        display: block;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 0.5rem;
        letter-spacing: 0.5px;
      }
      .input-wrapper {
        position: relative;
      }
      .input-wrapper input {
        width: 100%;
        padding: 1rem 1.25rem;
        background: #f4f5f7;
        border: none;
        border-radius: 4px;
        font-family: "Inter", sans-serif;
        font-size: 1rem;
        font-weight: 500;
        outline: none;
      }
      .input-icon-right {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 1.2rem;
        cursor: pointer;
      }

      .auth-btn {
        width: 100%;
        background: #222;
        color: #fff;
        border: none;
        padding: 1.25rem;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        align-items: center;
        cursor: pointer;
        margin-top: 2.5rem;
        margin-bottom: 2rem;
        transition: background 0.2s;
      }
      .auth-btn:hover {
        background: #000;
      }

      .back-login {
        display: block;
        text-align: center;
        text-decoration: none;
        color: #111;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
      }

      .reset-footer {
        padding: 2rem 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid transparent;
      }
      .reset-footer-text {
        font-size: 0.65rem;
        color: #777;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .reset-footer-links {
        display: flex;
        gap: 2rem;
      }
      .reset-footer-links a {
        color: #777;
        text-decoration: none;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      @media (max-width: 768px) {
        .reset-header,
        .reset-footer {
          flex-direction: column;
          gap: 1rem;
          text-align: center;
        }
        .auth-card {
          padding: 2rem;
        }
      }
    </style>
  </head>

  <body>
    <header class="reset-header">
      <a href="index.php" style="text-decoration: none"
        ><div class="reset-logo">Codemandu</div></a
      >
      <a href="support.php" class="nav-icon"
        ><span class="material-symbols-outlined">help</span></a
      >
    </header>

    <main class="main-content">
      <div class="auth-card">
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">
          Please enter a secure new password for your CarCodemandu account.
        </p>

        <form action="#" method="POST">
          <div class="form-group">
            <label>New Password</label>
            <div class="input-wrapper">
              <input type="password" placeholder="••••••••" />
              <span class="material-symbols-outlined input-icon-right"
                >visibility</span
              >
            </div>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <div class="input-wrapper">
              <input type="password" placeholder="••••••••" />
              <span class="material-symbols-outlined input-icon-right"
                >visibility</span
              >
            </div>
          </div>

          <button class="auth-btn">
            <span>Reset Password</span>
            <span class="material-symbols-outlined">arrow_forward</span>
          </button>

          <a href="login.php" class="back-login">Back to Login</a>
        </form>
      </div>
    </main>

    <footer class="reset-footer">
      <div class="reset-footer-text">
        © 2024 VANTAGE ARCHITECTURAL MOTORS. ALL RIGHTS RESERVED.
      </div>
      <div class="reset-footer-links">
        <a href="privacy.php">Privacy Policy</a>
        <a href="terms.php">Terms of Service</a>
        <a href="#">Security Protocol</a>
      </div>
    </footer>
  
<?php include 'view/layout/auth_footer.php'; ?>
