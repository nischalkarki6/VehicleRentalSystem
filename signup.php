<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

if (isset($_SESSION["user_id"])) {
    redirect($_SESSION["role"] === "admin" ? "admin.php" : "dashboard.php");
}

$errors = [];
$old = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
        $errors["form"] = "Invalid request. Please try again.";
    } else {
    $data = [
        "fullname" => trim($_POST["fullname"] ?? ""),
        "email" => trim($_POST["email"] ?? ""),
        "phone" => trim($_POST["phone"] ?? ""),
        "address" => trim($_POST["address"] ?? ""),
        "password" => $_POST["password"] ?? "",
        "confirm_password" => $_POST["confirm_password"] ?? "",
    ];

    foreach (["fullname", "email", "phone", "address"] as $k) {
        $old[$k] = htmlspecialchars($data[$k]);
    }

    $auth = new AuthController($pdo);
    $result = $auth->register($data);

    if ($result["success"]) {
        $emailParam = urlencode($data["email"]);
        redirect("verify_email.php?email=" . $emailParam);
    } else {
        $errors = $result["errors"];
    }
    }
}

$flash = getFlash();
$title = "Sign Up | DriveEase";
$css = "signup";
$authCss = true;
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <div class="overlay"></div>
    <div class="auth-card">
      <a href="index.php" class="close-btn">
        <span class="material-symbols-outlined">close</span>
      </a>
      <div class="auth-logo">DriveEase</div>
      <h1 class="auth-title">Create an account</h1>
      <p class="auth-subtitle">
        Start your journey with premium access today and experience
        architectural mobility.
      </p>

      <div class="auth-tabs">
        <a href="signup.php" class="auth-tab active">Sign Up</a>
        <a href="login.php"  class="auth-tab">Login</a>
      </div>

      <?php if ($flash && $flash["type"] === "error"): ?>
        <div class="alert alert-error">
          <span class="material-symbols-outlined">error</span>
          <?= htmlspecialchars($flash["message"]) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors["form"])): ?>
        <div class="alert alert-error">
          <span class="material-symbols-outlined">error</span>
          <?= htmlspecialchars($errors["form"]) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors["email"])): ?>
        <div class="alert alert-error">
          <span class="material-symbols-outlined">error</span>
          <?= htmlspecialchars($errors["email"]) ?>
        </div>
      <?php endif; ?>

      <form action="signup.php" method="POST" id="signupForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-row">
          <div class="form-group">
            <label for="fullname">Full Name</label>
            <div class="input-wrapper">
              <input type="text" id="fullname" name="fullname"
                     placeholder="Enter full name"
                     value="<?= $old["fullname"] ?? "" ?>"
                     class="<?= isset($errors["fullname"])
                         ? "input-error"
                         : "" ?>"
                     required />
            </div>
            <?php if (!empty($errors["fullname"])): ?>
              <span class="field-error"><?= htmlspecialchars(
                  $errors["fullname"],
              ) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <div class="input-wrapper">
              <input type="tel" id="phone" name="phone"
                     placeholder="Enter your phone no."
                     value="<?= $old["phone"] ?? "" ?>"
                     class="<?= isset($errors["phone"]) ? "input-error" : "" ?>"
                     required />
            </div>
            <?php if (!empty($errors["phone"])): ?>
              <span class="field-error"><?= htmlspecialchars(
                  $errors["phone"],
              ) ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email"
                   placeholder="Enter email"
                   value="<?= $old["email"] ?? "" ?>"
                   class="<?= isset($errors["email"]) ? "input-error" : "" ?>"
                   required />
          </div>
          <?php if (!empty($errors["email"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $errors["email"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="address">Home Address <span class="optional-label">(optional)</span></label>
          <div class="input-wrapper">
            <input type="text" id="address" name="address"
                   placeholder="Enter address"
                   value="<?= $old["address"] ?? "" ?>" />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password"
                   placeholder="Min. 8 characters"
                   class="<?= isset($errors["password"])
                       ? "input-error"
                       : "" ?>"
                   required />
            <span class="material-symbols-outlined input-icon-right"
                  data-password-toggle="#password">visibility_off</span>
          </div>
          <?php if (!empty($errors["password"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $errors["password"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group mb-2">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrapper">
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repeat password"
                   class="<?= isset($errors["confirm_password"])
                       ? "input-error"
                       : "" ?>"
                   required />
            <span class="material-symbols-outlined input-icon-right"
                  data-password-toggle="#confirm_password">visibility_off</span>
          </div>
          <?php if (!empty($errors["confirm_password"])): ?>
            <span class="field-error"><?= htmlspecialchars(
                $errors["confirm_password"],
            ) ?></span>
          <?php endif; ?>
        </div>

        <button class="auth-btn btn-primary" type="submit" id="signupBtn">
          <span>Create Account</span>
          <span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </form>

      <p class="auth-footer-text">
        By continuing, you agree to DriveEase's
        <a href="terms.php">Terms of Service</a> and
        <a href="privacy.php">Privacy Policy</a>.
      </p>
    </div>

<?php include "view/layout/auth_footer.php"; ?>
