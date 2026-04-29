<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

// Redirect already-logged-in users
if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$errors = [];
$old = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $old["email"] = htmlspecialchars($email);

    $auth = new AuthController($pdo);
    $result = $auth->login($email, $password);

    if ($result["success"]) {
        setFlash("success", "Welcome back, " . $_SESSION["user_name"] . "!");
        redirect($result["role"] === "admin" ? "admin.php" : "dashboard.php");
    } else {
        $errors["form"] = $result["error"];
    }
}

$flash = getFlash();
$title = "Login | DriveEase";
$css = "login";
$js = "login";
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

      <?php if ($flash && $flash["type"] === "success"): ?>
        <div class="alert alert-success">
          <span class="material-symbols-outlined">check_circle</span>
          <?= htmlspecialchars($flash["message"]) ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" id="loginForm" novalidate>
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
            <input type="password" id="password" name="password" placeholder="••••••••"
                   class="<?= isset($errors["password"])
                       ? "input-error"
                       : "" ?>"
                   required autocomplete="current-password" />
            <span class="material-symbols-outlined" id="togglePassword"
                  style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:#999;font-size:1.2rem;cursor:pointer;">
              visibility_off
            </span>
          </div>
          <a href="reset.php" class="forgot-pass">Forgot Password?</a>
        </div>

        <button class="auth-btn" type="submit" id="loginBtn">
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
