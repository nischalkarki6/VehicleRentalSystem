<?php
require_once "config/config.php";
require_once "models/User.php";

$step = "request"; // request | reset
$error = "";
$success = "";
$token = $_GET["token"] ?? "";

// If token is in the URL, show the reset form
if (!empty($token)) {
    $stmt = $pdo->prepare(
        "SELECT pr.*, u.Email FROM PasswordResets pr JOIN Users u ON pr.UserID = u.UserID WHERE pr.Token = ? AND pr.Used = 0 AND pr.ExpiresAt > NOW()",
    );
    $stmt->execute([$token]);
    $resetRow = $stmt->fetch();
    if ($resetRow) {
        $step = "reset";
    } else {
        $error = "Invalid or expired reset link. Please request a new one.";
    }
}

// Handle POST: request reset or set new password
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["reset_action"] ?? "";

    if ($action === "request") {
        $email = strtolower(trim($_POST["email"] ?? ""));
        $userModel = new User($pdo);
        $user = $userModel->findByEmail($email);
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $stmt = $pdo->prepare(
                "INSERT INTO PasswordResets (UserID, Token, ExpiresAt) VALUES (?, ?, ?)",
            );
            $stmt->execute([$user["UserID"], $token, $expires]);
            $resetLink =
                "http://" .
                ($_SERVER["HTTP_HOST"] ?? "localhost") .
                "/VRS-php/reset.php?token=" .
                $token;
            $success =
                "Reset link generated! Copy this link to reset your password:<br><code style='word-break:break-all;font-size:0.8rem'>" .
                htmlspecialchars($resetLink) .
                "</code>";
        } else {
            // Don't reveal whether email exists
            $success =
                "If an account with that email exists, a reset link has been generated.";
        }
    }

    if ($action === "reset") {
        $token = $_POST["token"] ?? "";
        $newPass = $_POST["new_password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";

        $stmt = $pdo->prepare(
            "SELECT pr.*, u.Password AS OldHash FROM PasswordResets pr JOIN Users u ON pr.UserID = u.UserID WHERE pr.Token = ? AND pr.Used = 0 AND pr.ExpiresAt > NOW()",
        );
        $stmt->execute([$token]);
        $resetRow = $stmt->fetch();

        if (!$resetRow) {
            $error = "Invalid or expired reset link.";
        } elseif (strlen($newPass) < 8) {
            $error = "Password must be at least 8 characters.";
            $step = "reset";
        } elseif (
            !preg_match("/[A-Z]/", $newPass) ||
            !preg_match("/[0-9]/", $newPass)
        ) {
            $error =
                "Password must contain at least one uppercase letter and one number.";
            $step = "reset";
        } elseif ($newPass !== $confirm) {
            $error = "Passwords do not match.";
            $step = "reset";
        } else {
            $hashed = password_hash($newPass, PASSWORD_BCRYPT, ["cost" => 12]);
            $pdo->prepare(
                "UPDATE Users SET Password = ? WHERE UserID = ?",
            )->execute([$hashed, $resetRow["UserID"]]);
            $pdo->prepare(
                "UPDATE PasswordResets SET Used = 1 WHERE ResetID = ?",
            )->execute([$resetRow["ResetID"]]);
            setFlash("success", "Password reset successfully! Please log in.");
            redirect("login.php");
        }
    }
}

$title = "Reset Password | DriveEase";
$css = "reset";
$js = "";
include "view/layout/auth_header.php";
?>
  </head>

  <body>
    <header class="reset-header">
      <a href="index.php" class="text-decoration-none"
        ><div class="reset-logo">DriveEase</div></a
      >
      <a href="support.php" class="nav-icon"
        ><span class="material-symbols-outlined">help</span></a
      >
    </header>

    <main class="main-content">
      <div class="auth-card">

        <?php if ($step === "request"): ?>
          <h1 class="auth-title">Reset Password</h1>
          <p class="auth-subtitle">
            Enter your email address and we'll generate a reset link for you.
          </p>

          <?php if ($error): ?>
            <div class="alert alert-error">
              <span class="material-symbols-outlined">error</span>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success">
              <span class="material-symbols-outlined">check_circle</span>
              <div><?= $success ?></div>
            </div>
          <?php endif; ?>

          <form action="reset.php" method="POST">
            <input type="hidden" name="reset_action" value="request">
            <div class="form-group">
              <label>Email Address</label>
              <div class="input-wrapper">
                <input type="email" name="email" placeholder="name@domain.com" required />
              </div>
            </div>

            <button class="auth-btn" type="submit">
              <span>Send Reset Link</span>
              <span class="material-symbols-outlined">arrow_forward</span>
            </button>

            <a href="login.php" class="back-login">Back to Login</a>
          </form>

        <?php else: ?>
          <h1 class="auth-title">Set New Password</h1>
          <p class="auth-subtitle">
            Please enter a secure new password for your DriveEase account.
          </p>

          <?php if ($error): ?>
            <div class="alert alert-error">
              <span class="material-symbols-outlined">error</span>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="reset.php" method="POST">
            <input type="hidden" name="reset_action" value="reset">
            <input type="hidden" name="token" value="<?= htmlspecialchars(
                $token,
            ) ?>">
            <div class="form-group">
              <label>New Password</label>
              <div class="input-wrapper">
                <input type="password" name="new_password" placeholder="••••••••" required />
                <span class="material-symbols-outlined input-icon-right">visibility</span>
              </div>
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <div class="input-wrapper">
                <input type="password" name="confirm_password" placeholder="••••••••" required />
                <span class="material-symbols-outlined input-icon-right">visibility</span>
              </div>
            </div>

            <button class="auth-btn" type="submit">
              <span>Reset Password</span>
              <span class="material-symbols-outlined">arrow_forward</span>
            </button>

            <a href="login.php" class="back-login">Back to Login</a>
          </form>
        <?php endif; ?>

      </div>
    </main>

    <footer class="reset-footer">
      <div class="reset-footer-text">
        © 2026 DRIVEEASE NEPAL. ALL RIGHTS RESERVED.
      </div>
      <div class="reset-footer-links">
        <a href="privacy.php">Privacy Policy</a>
        <a href="terms.php">Terms of Service</a>
      </div>
    </footer>
  
<?php include "view/layout/auth_footer.php"; ?>
