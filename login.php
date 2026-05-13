<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

// Redirect already-logged-in users
if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$errors = [];
$old = [];
$redirectUrl = $_GET["redirect"] ?? ($_POST["redirect"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $errors["form"] = "Invalid request. Please try again.";
    } else {
        $auth = new AuthController($pdo);
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        $old["email"] = htmlspecialchars($email);

        $result = $auth->login($email, $password);

        if ($result["success"]) {
            setFlash("success", "Welcome back, " . $_SESSION["user_name"] . "!");
            // Honor the redirect URL if provided and safe
            $safeRedirect = "";
            if (!empty($redirectUrl) && strpos($redirectUrl, '/') !== 0 && strpos($redirectUrl, 'http') !== 0) {
                $safeRedirect = $redirectUrl;
            }
            if ($safeRedirect) {
                redirect($safeRedirect);
            } else {
                redirect($result["role"] === "admin" ? "admin.php" : "dashboard.php");
            }
        } else {
            $errors["form"] = $result["error"];

            // If the user is unverified, show a resend option
            if (!empty($result["unverified"])) {
                $errors["unverified"] = true;
                $errors["unverified_email"] = $result["email"];
            }
        }
    }
}

$flash = getFlash();
$title = "Login | DriveEase";
$css = "login";
$authCss = true;
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card">
      <div class="auth-logo">DriveEase</div>
      <div class="auth-subtitle">Premium Vehicle Management</div>

      <div class="auth-tabs">
        <a href="login.php"  class="auth-tab active">Login</a>
        <a href="signup.php" class="auth-tab">Sign Up</a>
      </div>

      <?php if (!empty($errors["form"])): ?>
        <div class="alert alert-error">
          <span class="material-symbols-outlined">error</span>
          <?= htmlspecialchars($errors["form"]) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors["unverified"])): ?>
        <div class="alert alert-error unverified-alert">
          <div class="unverified-alert__title">
            <span class="material-symbols-outlined">mail</span>
            <span>Your email is not verified yet.</span>
          </div>
          <a href="verify_email.php?email=<?= urlencode($errors["unverified_email"] ?? "") ?>"
             class="unverified-alert__link">
            Verify with email code
          </a>
        </div>
      <?php endif; ?>

      <?php if ($flash && $flash["type"] === "success"): ?>
        <div class="alert alert-success">
          <span class="material-symbols-outlined">check_circle</span>
          <?= htmlspecialchars($flash["message"]) ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" placeholder="name@domain.com"
                   value="<?= $old["email"] ?? "" ?>"
                   class="<?= isset($errors["email"]) ? "input-error" : "" ?>"
                   required autocomplete="email" />
          </div>
          <?php if (!empty($errors["email"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $errors["email"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group mb-0">
          <label for="password">Password</label>
          <div class="input-wrapper pos-relative">
            <input type="password" id="password" name="password" placeholder="********"
                   class="<?= isset($errors["password"])
                       ? "input-error"
                       : "" ?>"
                   required autocomplete="current-password" />
            <span class="material-symbols-outlined input-icon-right"
                  data-password-toggle="#password">
              visibility_off
            </span>
          </div>
          <a href="forgot_password.php" class="forgot-pass">Forgot Password?</a>
        </div>

        <button class="auth-btn btn-primary" type="submit" id="loginBtn">
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

<?php include "view/layout/auth_footer.php"; ?>
