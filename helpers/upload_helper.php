<?php
/**
 * Upload Helper — Reusable image upload utility for Vehicle images.
 *
 * Validates MIME type using finfo (not browser-supplied type),
 * enforces a 2 MB max file size, and generates collision-free filenames.
 */

define("UPLOAD_DIR", __DIR__ . "/../uploads/vehicles/");
define("UPLOAD_REL_DIR", "uploads/vehicles/"); // Relative path stored in DB
define("MAX_FILE_SIZE", 2 * 1024 * 1024); // 2 MB

// Allowed MIME types → file extensions
define("ALLOWED_TYPES", [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp",
]);

/**
 * Upload a vehicle image from $_FILES input.
 *
 * @param  array $file  The $_FILES['image'] array
 * @return array        ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function uploadVehicleImage(array $file): array
{
    // ── Check for upload errors ──────────────────────────────────────────────
    if (!isset($file["tmp_name"]) || $file["error"] !== UPLOAD_ERR_OK) {
        $msg = match ($file["error"] ?? UPLOAD_ERR_NO_FILE) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => "File exceeds the maximum upload size.",
            UPLOAD_ERR_PARTIAL   => "File was only partially uploaded.",
            UPLOAD_ERR_NO_FILE   => "No file was selected.",
            default              => "An unknown upload error occurred.",
        };
        return ["success" => false, "path" => null, "error" => $msg];
    }

    // ── File size check ──────────────────────────────────────────────────────
    if ($file["size"] > MAX_FILE_SIZE) {
        return [
            "success" => false,
            "path"    => null,
            "error"   => "Image must be under 2 MB.",
        ];
    }

    // ── MIME type validation via finfo (server-side, not browser-supplied) ───
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file["tmp_name"]);

    if (!array_key_exists($mime, ALLOWED_TYPES)) {
        return [
            "success" => false,
            "path"    => null,
            "error"   => "Only JPG, PNG, and WEBP images are allowed.",
        ];
    }

    $ext = ALLOWED_TYPES[$mime];

    // ── Ensure upload directory exists ────────────────────────────────────────
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // ── Generate unique filename ─────────────────────────────────────────────
    $uniqueName = "vehicle_" . uniqid("", true) . "." . $ext;
    $destPath   = UPLOAD_DIR . $uniqueName;

    // ── Move the uploaded file ───────────────────────────────────────────────
    if (!move_uploaded_file($file["tmp_name"], $destPath)) {
        return [
            "success" => false,
            "path"    => null,
            "error"   => "Failed to save the uploaded file.",
        ];
    }

    return [
        "success" => true,
        "path"    => UPLOAD_REL_DIR . $uniqueName,
        "error"   => null,
    ];
}

/**
 * Delete a vehicle image from disk.
 *
 * @param  string|null $relativePath  The relative path stored in the DB (e.g. uploads/vehicles/vehicle_abc123.jpg)
 * @return bool                       True if deleted or path was empty/null
 */
function deleteVehicleImage(?string $relativePath): bool
{
    if (empty($relativePath)) {
        return true; // Nothing to delete
    }

    $fullPath = __DIR__ . "/../" . $relativePath;

    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }

    return true; // File doesn't exist — treat as success
}
