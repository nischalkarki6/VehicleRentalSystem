<?php
// Include guard - prevents double PDO init when header.php re-includes
if (defined("CONFIG_LOADED")) {
    return;
}
define("CONFIG_LOADED", true);

// -- Load .env File -----------------------------------------------------------
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            $_ENV[$key]    = $value;
            putenv("$key=$value");
        }
    }
}

// Database Connection
$host = "localhost";
$db = "vrs";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => false,
        "httponly" => true,
        "samesite" => "Strict",
    ]);
    session_start();
}

// Flash Messages
function setFlash($type, $message)
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}
function getFlash()
{
    $flash = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    return $flash;
}

// Redirection & Auth Helpers
function redirect($url)
{
    header("Location: $url");
    exit();
}

function getAppBaseUrl(): string
{
    $configuredUrl = trim($_ENV["APP_URL"] ?? "");
    if ($configuredUrl !== "") {
        return rtrim($configuredUrl, "/");
    }

    $host = $_SERVER["HTTP_HOST"] ?? "";
    if ($host !== "") {
        $isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
            || (($_SERVER["SERVER_PORT"] ?? "") === "443");
        $scheme = $isHttps ? "https" : "http";
        $scriptDir = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? ""));
        $basePath = $scriptDir === "/" || $scriptDir === "." ? "" : rtrim($scriptDir, "/");

        return $scheme . "://" . $host . $basePath;
    }

    return "http://localhost/VRS-php";
}

function buildAppUrl(string $path): string
{
    return getAppBaseUrl() . "/" . ltrim($path, "/");
}

function requireLogin()
{
    if (!isset($_SESSION["user_id"])) {
        redirect("login.php");
    }
}
function requireAdmin()
{
    requireLogin();
    if (($_SESSION["role"] ?? "") !== "admin") {
        redirect("dashboard.php");
    }
}

// CSRF Token Helpers
function generateCsrfToken()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verifyCsrfToken($token)
{
    return isset($_SESSION["csrf_token"]) &&
        hash_equals($_SESSION["csrf_token"], $token);
}

// -- Include Helpers -----------------------------------------------------------
require_once __DIR__ . "/../helpers/upload_helper.php";
require_once __DIR__ . "/../helpers/mail_helper.php";
