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
     * Register a new user
     */
    public function register(array $data): array
    {
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        // Check if email exists
        if ($this->userModel->findByEmail($data["email"])) {
            return [
                "success" => false,
                "errors" => ["email" => "Email already registered."],
            ];
        }

        $data["password"] = password_hash($data["password"], PASSWORD_BCRYPT, [
            "cost" => 12,
        ]);

        if ($this->userModel->create($data)) {
            $user = $this->userModel->findByEmail($data["email"]);
            return ["success" => true, "user" => $user];
        }

        return [
            "success" => false,
            "errors" => ["form" => "Registration failed. Please try again."],
        ];
    }

    /**
     * Authenticate user
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return [
                "success" => false,
                "error" => "No account found with this email. Please sign up.",
            ];
        }

        if (!password_verify($password, $user["Password"])) {
            return [
                "success" => false,
                "error" => "Incorrect password. Please try again.",
            ];
        }

        // Setup session
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["UserID"];
        $_SESSION["user_name"] = $user["FullName"];
        $_SESSION["role"] = $user["Role"];

        return ["success" => true, "role" => $user["Role"]];
    }

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
