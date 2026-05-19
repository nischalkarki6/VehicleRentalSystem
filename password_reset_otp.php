<?php
require_once "config/config.php";
require_once "controllers/AuthController.php";

header("Content-Type: application/json; charset=utf-8");
header("X-Content-Type-Options: nosniff");

function readPasswordResetOtpPayload(): array
{
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    if (str_contains(strtolower($contentType), "application/json")) {
        $payload = json_decode(file_get_contents("php://input"), true);
        return is_array($payload) ? $payload : [];
    }

    return $_POST;
}

function sendPasswordResetOtpJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function isPasswordResetOtpRateLimited(
    PDO $pdo,
    string $action,
    int $maxAttempts,
    int $windowSeconds
): bool {
    $ipAddress = substr($_SERVER["REMOTE_ADDR"] ?? "unknown", 0, 45);
    $windowCutoff = date("Y-m-d H:i:s", time() - $windowSeconds);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT ID, Attempts, WindowStart
             FROM RateLimits
             WHERE IPAddress = ? AND Action = ?
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([$ipAddress, $action]);
        $row = $stmt->fetch() ?: null;

        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO RateLimits (IPAddress, Action, Attempts, WindowStart)
                 VALUES (?, ?, 1, NOW())"
            );
            $insert->execute([$ipAddress, $action]);
            $pdo->commit();
            return false;
        }

        if ($row["WindowStart"] < $windowCutoff) {
            $reset = $pdo->prepare(
                "UPDATE RateLimits
                 SET Attempts = 1, WindowStart = NOW()
                 WHERE ID = ?"
            );
            $reset->execute([$row["ID"]]);
            $pdo->commit();
            return false;
        }

        if ((int) $row["Attempts"] >= $maxAttempts) {
            $pdo->commit();
            return true;
        }

        $update = $pdo->prepare(
            "UPDATE RateLimits
             SET Attempts = Attempts + 1
             WHERE ID = ?"
        );
        $update->execute([$row["ID"]]);
        $pdo->commit();
        return false;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[password_reset_otp] Rate limit unavailable: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendPasswordResetOtpJson([
        "success" => false,
        "error" => "Method not allowed.",
    ], 405);
}

$payload = readPasswordResetOtpPayload();

if (!verifyCsrfToken($payload["csrf_token"] ?? "")) {
    sendPasswordResetOtpJson([
        "success" => false,
        "error" => "Invalid request. Please refresh and try again.",
    ], 403);
}

$auth = new AuthController($pdo);
$action = $payload["action"] ?? "";

if ($action === "request") {
    if (isPasswordResetOtpRateLimited($pdo, "password_reset_otp_request", 5, 3600)) {
        sendPasswordResetOtpJson([
            "success" => false,
            "error" => "Too many reset requests. Please try again later.",
        ], 429);
    }

    $result = $auth->requestPasswordResetOtp($payload["email"] ?? "");
    sendPasswordResetOtpJson([
        "success" => (bool) $result["success"],
        "message" => $result["message"],
    ], $result["success"] ? 200 : 500);
}

if ($action === "verify") {
    if (isPasswordResetOtpRateLimited($pdo, "password_reset_otp_verify", 10, 600)) {
        sendPasswordResetOtpJson([
            "success" => false,
            "error" => "Too many verification attempts. Please request a new code.",
        ], 429);
    }

    $result = $auth->verifyPasswordResetOtp(
        $payload["email"] ?? "",
        $payload["otp"] ?? ""
    );

    if (!$result["success"]) {
        sendPasswordResetOtpJson($result, 400);
    }

    sendPasswordResetOtpJson($result);
}

if ($action === "reset") {
    $result = $auth->completePasswordResetOtp(
        $payload["reset_session_token"] ?? "",
        $payload["password"] ?? "",
        $payload["confirm_password"] ?? ""
    );

    sendPasswordResetOtpJson($result, $result["success"] ? 200 : 400);
}

sendPasswordResetOtpJson([
    "success" => false,
    "error" => "Invalid password reset action.",
], 400);
