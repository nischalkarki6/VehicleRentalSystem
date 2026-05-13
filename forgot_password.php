<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

// Redirect already-logged-in users
if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$status  = "form";
$message = "";
$old     = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $status  = "form";
        $message = "Invalid request. Please try again.";
    } else {
        $email       = trim($_POST["email"] ?? "");
        $old["email"] = htmlspecialchars($email);

        $auth   = new AuthController($pdo);
        $result = $auth->requestPasswordReset($email);

        // Always show "sent" to prevent enumeration
        $status  = "sent";
        $message = $result["message"];
    }
}

$title = "Forgot Password | DriveEase";
$css   = "forgot_password";
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
        <!-- -- Email Sent Confirmation ---------------------------------- -->
        <div class="verify-icon verify-icon--info">
          <span class="material-symbols-outlined">mark_email_read</span>
        </div>
        <h1 class="verify-title">Check Your Inbox</h1>
        <p class="verify-text">
          <?= htmlspecialchars($message) ?>
        </p>
        <p class="verify-text forgot-note">
          The link will expire in 60 minutes. Check your spam folder if you don't see it.
        </p>
        <a href="login.php" class="auth-btn btn-primary" id="backToLoginBtn">
          <span>Back to Login</span>
          <span class="material-symbols-outlined">arrow_back</span>
        </a>

      <?php else: ?>
        <!-- -- Request Reset Form --------------------------------------- -->
        <div class="verify-icon verify-icon--info">
          <span class="material-symbols-outlined">lock_reset</span>
        </div>
        <h1 class="verify-title">Forgot Password?</h1>
        <p class="verify-text">
          Enter the email address associated with your account and we'll send you
          a link to reset your password.
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
            <span>Send Reset Link</span>
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
