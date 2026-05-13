<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../models/User.php";

class AuthController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    /**
     * Register a new user.
     * Account is NOT active until the email is verified.
     * A verification email is sent via PHPMailer SMTP.
     */
    public function register(array $data): array
    {
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        // Check if email exists
        $existing = $this->userModel->findByEmail($data["email"]);
        if ($existing) {
            return [
                "success" => false,
                "errors" => ["email" => "Email already registered."],
            ];
        }

        $data["password"] = password_hash($data["password"], PASSWORD_BCRYPT, [
            "cost" => 12,
        ]);

        $isAdmin = ($data["role"] ?? "user") === "admin";
        $otp = $isAdmin ? "" : (string) random_int(100000, 999999);
        $tokenHash = $isAdmin ? "" : hash('sha256', $otp);

        if ($this->userModel->create($data, $tokenHash)) {
            $sent = true;
            if (!$isAdmin) {
                $emailHandler = new EmailHandler();
                $sent = $emailHandler->sendVerificationEmail(
                    $data["email"],
                    $data["fullname"],
                    $otp
                );
            }

            if (!$sent && !$isAdmin) {
                error_log('[AuthController] Verification OTP for ' . $data["email"] . ': ' . $otp);
            }

            return [
                "success"    => true,
                "email_sent" => $sent,
                "email"      => $data["email"],
            ];
        }

        return [
            "success" => false,
            "errors" => ["form" => "Registration failed. Please try again."],
        ];
    }

    // -- Email Verification ---------------------------------------------------

    /**
     * Verify a user's email using a one-time code.
     */
    public function verifyEmail(string $email, string $otp): array
    {
        $tokenHash = hash('sha256', $otp);
        $user = $this->userModel->findByVerificationToken($tokenHash, $email);

        if (!$user) {
            return [
                "success" => false,
                "error"   => "Invalid or expired verification code. Please request a new one.",
            ];
        }

        if ($this->userModel->markVerified($user["UserID"])) {
            return ["success" => true, "user" => $user];
        }

        return [
            "success" => false,
            "error"   => "Verification failed. Please try again.",
        ];
    }

    /**
     * Resend verification code.
     */
    public function resendVerification(string $email): array
    {
        // Always return success-like message to prevent user enumeration
        $genericMessage = "If that email is registered and unverified, a new verification code has been sent.";

        $user = $this->userModel->findByEmail($email);
        if (!$user || ($user["Role"] ?? "") === "admin" || ($user["IsVerified"] ?? 1)) {
            return ["success" => true, "message" => $genericMessage];
        }

        $otp = (string) random_int(100000, 999999);
        $tokenHash = hash('sha256', $otp);

        $this->userModel->updateVerificationToken($user["UserID"], $tokenHash);

        $emailHandler = new EmailHandler();
        $emailHandler->sendVerificationEmail(
            $user["Email"],
            $user["FullName"],
            $otp
        );

        return ["success" => true, "message" => $genericMessage];
    }

    // -- Password Reset -------------------------------------------------------

    /**
     * Request a password reset. Sends reset email if user exists.
     * IMPORTANT: Never reveals whether the email exists (prevents enumeration).
     */
    public function requestPasswordReset(string $email): array
    {
        $genericMessage = "If an account with that email exists, a password reset link has been sent.";

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            // Don't reveal that the user doesn't exist
            return ["success" => true, "message" => $genericMessage];
        }

        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $this->userModel->createPasswordReset($user["UserID"], $tokenHash);

        $emailHandler = new EmailHandler();
        $sent = $emailHandler->sendPasswordResetEmail(
            $user["Email"],
            $user["FullName"],
            $rawToken
        );

        if (!$sent) {
            $link = buildAppUrl('reset_password.php?token=' . urlencode($rawToken));
            error_log('[AuthController] Reset link (email failed): ' . $link);
        }

        return ["success" => true, "message" => $genericMessage];
    }

    /**
     * Validate a reset token (check if it's valid and not expired).
     */
    public function validateResetToken(string $rawToken): array
    {
        $tokenHash = hash('sha256', $rawToken);
        $reset = $this->userModel->findPasswordReset($tokenHash);

        if (!$reset) {
            return [
                "success" => false,
                "error"   => "Invalid or expired reset link. Please request a new one.",
            ];
        }

        return ["success" => true, "reset" => $reset];
    }

    /**
     * Complete the password reset: validate token, hash new password, save.
     */
    public function resetPassword(string $rawToken, string $newPassword, string $confirmPassword): array
    {
        // Validate token
        $validation = $this->validateResetToken($rawToken);
        if (!$validation["success"]) {
            return $validation;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return [
                "success" => false,
                "error"   => "Password must be at least 8 characters.",
            ];
        }

        if (!preg_match("/[A-Z]/", $newPassword) || !preg_match("/[0-9]/", $newPassword)) {
            return [
                "success" => false,
                "error"   => "Password must contain at least one uppercase letter and one number.",
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                "success" => false,
                "error"   => "Passwords do not match.",
            ];
        }

        $reset = $validation["reset"];
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 12]);

        if ($this->userModel->resetPassword($reset["ResetID"], $reset["UserID"], $hashedPassword)) {
            return ["success" => true];
        }

        return [
            "success" => false,
            "error"   => "Password reset failed. Please try again.",
        ];
    }

    // -- Login ----------------------------------------------------------------

    /**
     * Validate credentials and start an authenticated session.
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return [
                "success" => false,
                "error" => "Invalid email or password.",
            ];
        }

        if (!password_verify($password, $user["Password"])) {
            return [
                "success" => false,
                "error" => "Invalid email or password.",
            ];
        }

        // Check if email is verified
        if (($user["Role"] ?? "") !== "admin" && isset($user["IsVerified"]) && !$user["IsVerified"]) {
            return [
                "success"    => false,
                "error"      => "Please verify your email before logging in.",
                "unverified" => true,
                "email"      => $user["Email"],
            ];
        }

        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["UserID"];
        $_SESSION["user_name"] = $user["FullName"];
        $_SESSION["role"] = $user["Role"];

        return ["success" => true, "role" => $user["Role"]];
    }

    // -------------------------------------------------------------------------
    // Profile Management
    // -------------------------------------------------------------------------

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): array
    {
        $errors = $this->validateProfile($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        if ($this->userModel->update($userId, $data)) {
            $_SESSION["user_name"] = $data["fullname"];
            return ["success" => true];
        }

        return ["success" => false, "errors" => ["form" => "Update failed."]];
    }

    /**
     * Change user password
     */
    public function changePassword(
        int $userId,
        string $current,
        string $new,
        string $confirm,
    ): array {
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($current, $user["Password"])) {
            return [
                "success" => false,
                "errors" => [
                    "current_password" => "Incorrect current password.",
                ],
            ];
        }

        if (strlen($new) < 8) {
            return [
                "success" => false,
                "errors" => [
                    "new_password" => "Password must be at least 8 characters.",
                ],
            ];
        }

        if (!preg_match("/[A-Z]/", $new) || !preg_match("/[0-9]/", $new)) {
            return [
                "success" => false,
                "errors" => [
                    "new_password" =>
                        "Password must contain at least one uppercase letter and one number.",
                ],
            ];
        }

        if ($new !== $confirm) {
            return [
                "success" => false,
                "errors" => ["confirm_password" => "Passwords do not match."],
            ];
        }

        // Prevent reusing the current password
        if (password_verify($new, $user["Password"])) {
            return [
                "success" => false,
                "errors" => [
                    "new_password" =>
                        "New password cannot be the same as your current password.",
                ],
            ];
        }

        $hashed = password_hash($new, PASSWORD_BCRYPT, ["cost" => 12]);
        if ($this->userModel->updatePassword($userId, $hashed)) {
            return ["success" => true];
        }

        return [
            "success" => false,
            "errors" => ["form" => "Password update failed."],
        ];
    }

    private function validateRegistration(array $d): array
    {
        $errors = [];
        $fullname = trim($d["fullname"] ?? "");
        if (empty($fullname)) {
            $errors["fullname"] = "Full name is required.";
        } elseif (strlen($fullname) < 2 || strlen($fullname) > 100) {
            $errors["fullname"] =
                "Full name must be between 2 and 100 characters.";
        }

        if (!filter_var($d["email"] ?? "", FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "Valid email is required.";
        }

        $phone = trim($d["phone"] ?? "");
        if (empty($phone)) {
            $errors["phone"] = "Phone is required.";
        } elseif (!preg_match('/^[\+]?[0-9\s\-]{7,20}$/', $phone)) {
            $errors["phone"] = "Invalid phone number format.";
        }

        $password = $d["password"] ?? "";
        if (strlen($password) < 8) {
            $errors["password"] = "Min 8 characters required.";
        } elseif (
            !preg_match("/[A-Z]/", $password) ||
            !preg_match("/[0-9]/", $password)
        ) {
            $errors["password"] =
                "Password must contain at least one uppercase letter and one number.";
        }

        if ($password !== ($d["confirm_password"] ?? "")) {
            $errors["confirm_password"] = "Passwords must match.";
        }

        return $errors;
    }

    private function validateProfile(array $d): array
    {
        $errors = [];
        $fullname = trim($d["fullname"] ?? "");
        if (empty($fullname)) {
            $errors["fullname"] = "Full name is required.";
        } elseif (strlen($fullname) < 2 || strlen($fullname) > 100) {
            $errors["fullname"] =
                "Full name must be between 2 and 100 characters.";
        }

        $phone = trim($d["phone"] ?? "");
        if (empty($phone)) {
            $errors["phone"] = "Phone is required.";
        } elseif (!preg_match('/^[\+]?[0-9\s\-]{7,20}$/', $phone)) {
            $errors["phone"] = "Invalid phone number format.";
        }

        return $errors;
    }
}
