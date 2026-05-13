<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

// Redirect already-logged-in users
if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$auth   = new AuthController($pdo);
$status = "form";
$error  = "";
$token  = $_GET["token"] ?? $_POST["token"] ?? "";

// Validate the reset token before showing the form.
if (empty($token)) {
    $status = "invalid";
    $error  = "No reset token provided. Please request a new password reset link.";
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    $validation = $auth->validateResetToken($token);
    if (!$validation["success"]) {
        $status = "invalid";
        $error  = $validation["error"];
    }
}

// Handle the password reset submission.
if ($_SERVER["REQUEST_METHOD"] === "POST" && $status !== "invalid") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $error = "Invalid request. Please try again.";
    } else {
        $password        = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        $result = $auth->resetPassword($token, $password, $confirmPassword);

        if ($result["success"]) {
            $status = "success";
        } else {
            $error = $result["error"];
        }
    }
}

$title = "Reset Password | DriveEase";
$css   = "reset_password";
$authCss = true;
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card reset-card">
      <a href="login.php" class="close-btn">
        <span class="material-symbols-outlined">close</span>
      </a>

      <?php if ($status === "success"): ?>
        <!-- -- Password Changed Successfully ---------------------------- -->
        <div class="verify-icon verify-icon--success">
          <span class="material-symbols-outlined">lock_open</span>
        </div>
        <h1 class="verify-title">Password Updated!</h1>
        <p class="verify-text">
          Your password has been changed successfully. You can now log in with your new password.
        </p>
        <a href="login.php" class="auth-btn btn-primary" id="loginAfterResetBtn">
          <span>Continue to Login</span>
          <span class="material-symbols-outlined">arrow_forward</span>
        </a>

      <?php elseif ($status === "invalid"): ?>
        <!-- -- Invalid / Expired Token ---------------------------------- -->
        <div class="verify-icon verify-icon--error">
          <span class="material-symbols-outlined">link_off</span>
        </div>
        <h1 class="verify-title">Link Expired</h1>
        <p class="verify-text">
          <?= htmlspecialchars($error) ?>
        </p>
        <a href="forgot_password.php" class="auth-btn btn-primary" id="requestNewResetBtn">
          <span>Request New Link</span>
          <span class="material-symbols-outlined">refresh</span>
        </a>

      <?php else: ?>
        <!-- -- Reset Password Form -------------------------------------- -->
        <div class="verify-icon verify-icon--info">
          <span class="material-symbols-outlined">password</span>
        </div>
        <h1 class="verify-title">Set New Password</h1>
        <p class="verify-text">
          Choose a strong password with at least 8 characters, one uppercase letter, and one number.
        </p>

        <?php if (!empty($error)): ?>
          <div class="alert alert-error">
            <span class="material-symbols-outlined">error</span>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="reset_password.php" method="POST" id="resetPasswordForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <div class="form-group">
            <label for="password">New Password</label>
            <div class="input-wrapper pos-relative">
              <input type="password" id="password" name="password"
                     placeholder="Min. 8 characters"
                     required autocomplete="new-password" />
              <span class="material-symbols-outlined input-icon-right"
                    data-password-toggle="#password">visibility_off</span>
            </div>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <div class="input-wrapper pos-relative">
              <input type="password" id="confirm_password" name="confirm_password"
                     placeholder="Repeat password"
                     required autocomplete="new-password" />
              <span class="material-symbols-outlined input-icon-right"
                    data-password-toggle="#confirm_password">visibility_off</span>
            </div>
          </div>

          <!-- Password strength indicator -->
          <div class="password-strength" id="passwordStrength">
            <div class="strength-bar">
              <div class="strength-fill" id="strengthFill"></div>
            </div>
            <span class="strength-text" id="strengthText"></span>
          </div>

          <button class="auth-btn btn-primary" type="submit" id="resetPasswordBtn">
            <span>Update Password</span>
            <span class="material-symbols-outlined">lock</span>
          </button>
        </form>
      <?php endif; ?>
    </div>

<?php include "view/layout/auth_footer.php"; ?>
