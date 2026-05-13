<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

$auth    = new AuthController($pdo);
$status  = "pending";
$message = "";
$email   = trim($_GET["email"] ?? $_POST["email"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $status  = "error";
        $message = "Invalid request. Please try again.";
    } elseif (isset($_POST["resend"])) {
        if ($email === "") {
            $status  = "error";
            $message = "Please enter your email address to receive a verification code.";
        } else {
            $result  = $auth->resendVerification($email);
            $status  = "resent";
            $message = $result["message"];
        }
    } else {
        $otp = trim($_POST["otp"] ?? "");
        if ($email === "" || $otp === "") {
            $status  = "error";
            $message = "Please enter your email address and verification code.";
        } else {
            $result = $auth->verifyEmail($email, $otp);
            if ($result["success"]) {
                $status  = "success";
                $message = "Your email has been verified! You can now log in.";
            } else {
                $status  = "error";
                $message = $result["error"];
            }
        }
    }
}

$title = "Verify Email | DriveEase";
$css   = "verify";
$authCss = true;
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card verify-card">
      <a href="index.php" class="close-btn">
        <span class="material-symbols-outlined">close</span>
      </a>

      <?php if ($status === "success"): ?>
        <div class="verify-icon verify-icon--success">
          <span class="material-symbols-outlined">verified</span>
        </div>
        <h1 class="verify-title">Email Verified!</h1>
        <p class="verify-text">
          Your account is now active. You can log in and start booking
          premium vehicles on DriveEase.
        </p>
        <a href="login.php" class="auth-btn btn-primary" id="goToLoginBtn">
          <span>Continue to Login</span>
          <span class="material-symbols-outlined">arrow_forward</span>
        </a>

      <?php else: ?>
        <div class="verify-icon verify-icon--info">
          <span class="material-symbols-outlined">mark_email_read</span>
        </div>
        <h1 class="verify-title">Verify Your Email</h1>

        <?php if ($status === "error"): ?>
          <p class="verify-text"><?= htmlspecialchars($message) ?></p>
        <?php elseif ($status === "resent"): ?>
          <p class="verify-text"><?= htmlspecialchars($message) ?></p>
        <?php else: ?>
          <p class="verify-text">
            We sent a verification code to
            <?php if ($email !== ""): ?>
              <strong><?= htmlspecialchars($email) ?></strong>.
            <?php else: ?>
              your email address.
            <?php endif; ?>
          </p>
        <?php endif; ?>

        <form action="verify_email.php" method="POST" class="resend-form" id="verifyOtpForm">
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

          <label for="verifyEmail" class="verify-label">Email address</label>
          <div class="input-wrapper">
            <input type="email" name="email" id="verifyEmail"
                   placeholder="name@domain.com"
                   value="<?= htmlspecialchars($email) ?>"
                   required autocomplete="email" />
          </div>

          <label for="otp" class="verify-label">Verification code</label>
          <div class="input-wrapper">
            <input type="text" name="otp" id="otp"
                   placeholder="Enter 6-digit code"
                   inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                   required autocomplete="one-time-code" />
          </div>

          <button type="submit" class="auth-btn btn-primary" id="verifyBtn">
            <span>Verify Email</span>
            <span class="material-symbols-outlined">verified_user</span>
          </button>
        </form>

        <form action="verify_email.php" method="POST" class="resend-form" id="resendFormPending">
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
          <p class="verify-resend-text">
            Didn't receive the code?
          </p>
          <button type="submit" name="resend" value="1"
                  class="btn-light resend-btn" id="resendBtnPending">
            <span class="material-symbols-outlined">refresh</span>
            <span>Resend Code</span>
          </button>
        </form>

        <a href="login.php" class="verify-login-link">
          <span class="material-symbols-outlined">arrow_back</span>
          Back to Login
        </a>
      <?php endif; ?>
    </div>

<?php include "view/layout/auth_footer.php"; ?>
