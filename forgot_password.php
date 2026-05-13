<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

// Redirect already-logged-in users
if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$status  = "form";
$message = "";
$email   = "";
$old     = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $status  = "form";
        $message = "Invalid request. Please try again.";
    } else {
        $email       = trim($_POST["email"] ?? "");
        $old["email"] = htmlspecialchars($email);

        $auth   = new AuthController($pdo);
        $result = $auth->requestPasswordResetOtp($email);

        // Always show "sent" to prevent enumeration
        $status  = "sent";
        $message = $result["message"];
    }
}

$title = "Forgot Password | DriveEase";
$css   = "forgot_password";
$js    = "forgot_password";
$authCss = true;
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card forgot-card">
      <a href="login.php" class="close-btn">
        <span class="material-symbols-outlined">close</span>
      </a>

      <?php if ($status === "sent"): ?>
        <section class="otp-panel" id="otpPanel">
          <div class="verify-icon verify-icon--info">
            <span class="material-symbols-outlined">mark_email_read</span>
          </div>
          <h1 class="verify-title">Enter Reset Code</h1>
          <p class="verify-text">
            <?= htmlspecialchars($message) ?>
          </p>
          <p class="verify-text forgot-note">
            The code sent to <strong><?= htmlspecialchars($email) ?></strong> expires in 10 minutes.
          </p>

          <form id="otpVerifyForm"
                class="otp-form"
                data-endpoint="password_reset_otp.php"
                data-email="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <label for="otpCode" class="verify-label">Verification code</label>
            <div class="input-wrapper">
              <input type="text" id="otpCode" name="otp"
                     class="otp-input"
                     inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                     placeholder="000000"
                     required autocomplete="one-time-code" />
            </div>

            <p class="form-message" id="otpMessage" aria-live="polite"></p>

            <button class="auth-btn btn-primary" type="submit" id="verifyOtpBtn">
              <span>Verify Code</span>
              <span class="material-symbols-outlined">verified_user</span>
            </button>
          </form>

          <a href="forgot_password.php" class="verify-login-link">
            <span class="material-symbols-outlined">refresh</span>
            Request another code
          </a>
        </section>

        <div class="reset-popup" id="resetPopup" aria-hidden="true">
          <div class="reset-dialog" role="dialog" aria-modal="true" aria-labelledby="resetPopupTitle">
            <div class="verify-icon verify-icon--success">
              <span class="material-symbols-outlined">lock_reset</span>
            </div>
            <h2 class="verify-title" id="resetPopupTitle">Reset Password</h2>
            <p class="verify-text">
              Choose a new password for your DriveEase account.
            </p>

            <form id="resetPasswordOtpForm" class="reset-form">
              <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
              <input type="hidden" name="reset_session_token" id="resetSessionToken">

              <div class="form-group">
                <label for="newPassword">New Password</label>
                <div class="input-wrapper pos-relative">
                  <input type="password" id="newPassword" name="password"
                         placeholder="Min. 8 characters"
                         required autocomplete="new-password" />
                  <span class="material-symbols-outlined input-icon-right"
                        data-password-toggle="#newPassword">visibility_off</span>
                </div>
              </div>

              <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-wrapper pos-relative">
                  <input type="password" id="confirmPassword" name="confirm_password"
                         placeholder="Repeat password"
                         required autocomplete="new-password" />
                  <span class="material-symbols-outlined input-icon-right"
                        data-password-toggle="#confirmPassword">visibility_off</span>
                </div>
              </div>

              <div class="password-strength" id="resetStrength">
                <div class="strength-bar">
                  <div class="strength-fill" id="resetStrengthFill"></div>
                </div>
                <span class="strength-text" id="resetStrengthText"></span>
              </div>

              <p class="form-message" id="resetMessage" aria-live="polite"></p>

              <button class="auth-btn btn-primary" type="submit" id="resetPasswordOtpBtn">
                <span>Update Password</span>
                <span class="material-symbols-outlined">lock</span>
              </button>
            </form>
          </div>
        </div>

      <?php else: ?>
        <!-- -- Request Reset Form --------------------------------------- -->
        <div class="verify-icon verify-icon--info">
          <span class="material-symbols-outlined">lock_reset</span>
        </div>
        <h1 class="verify-title">Forgot Password?</h1>
        <p class="verify-text">
          Enter the email address associated with your account and we'll send you
          a code to reset your password.
        </p>

        <?php if (!empty($message)): ?>
          <div class="alert alert-error">
            <span class="material-symbols-outlined">error</span>
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST" id="forgotForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrapper">
              <input type="email" id="email" name="email"
                     placeholder="name@domain.com"
                     value="<?= $old["email"] ?? "" ?>"
                     required autocomplete="email" />
            </div>
          </div>

          <button class="auth-btn btn-primary" type="submit" id="resetRequestBtn">
            <span>Send Reset Code</span>
            <span class="material-symbols-outlined">send</span>
          </button>
        </form>

        <a href="login.php" class="verify-login-link">
          <span class="material-symbols-outlined">arrow_back</span>
          Back to Login
        </a>
      <?php endif; ?>
    </div>

<?php include "view/layout/auth_footer.php"; ?>
