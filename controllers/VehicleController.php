<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../models/Vehicle.php";
require_once __DIR__ . "/../helpers/upload_helper.php";

class VehicleController
{
    private Vehicle $vehicleModel;

    public function __construct(PDO $pdo)
    {
        $this->vehicleModel = new Vehicle($pdo);
    }

    public function getAllVehicles(): array
    {
        return $this->vehicleModel->getAll();
    }

    public function getAvailableVehicles(): array
    {
        return $this->vehicleModel->getAvailable();
    }

    public function getByCategory(string $cat): array
    {
        return $this->vehicleModel->getByCategory($cat);
    }

    public function addVehicle(array $data, array $fileInput): array
    {
        $errors = $this->validateVehicle($data);

        // -- Handle image upload ----------------------------------------------
        $imagePath = null;
        if (
            isset($fileInput["tmp_name"]) &&
            $fileInput["error"] !== UPLOAD_ERR_NO_FILE
        ) {
            $upload = uploadVehicleImage($fileInput);
            if (!$upload["success"]) {
                $errors["image"] = $upload["error"];
            } else {
                $imagePath = $upload["path"];
            }
        }

        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        $data["image_url"] = $imagePath;

        if ($this->vehicleModel->create($data)) {
            return ["success" => true];
        }

        // Cleanup uploaded file on DB failure
        if ($imagePath) {
            deleteVehicleImage($imagePath);
        }

        return [
            "success" => false,
            "errors" => ["form" => "Failed to add vehicle."],
        ];
    }

    public function updateVehicle(int $id, array $data, array $fileInput): array
    {
        $errors = $this->validateVehicle($data);

        // -- Handle image upload on edit --------------------------------------
        $newImagePath = null;
        $hasNewUpload = isset($fileInput["tmp_name"]) &&
                        $fileInput["error"] !== UPLOAD_ERR_NO_FILE;

        if ($hasNewUpload) {
            $upload = uploadVehicleImage($fileInput);
            if (!$upload["success"]) {
                $errors["image"] = $upload["error"];
            } else {
                $newImagePath = $upload["path"];
            }
        }

        if (!empty($errors)) {
            // Cleanup newly uploaded file if validation fails
            if ($newImagePath) {
                deleteVehicleImage($newImagePath);
            }
            return ["success" => false, "errors" => $errors];
        }

        // If a new image was uploaded, delete the old one
        if ($newImagePath) {
            $existing = $this->vehicleModel->findById($id);
            if ($existing && !empty($existing["ImageURL"])) {
                deleteVehicleImage($existing["ImageURL"]);
            }
            $data["image_url"] = $newImagePath;
        } else {
            // Keep the existing image - don't overwrite with null
            $existing = $this->vehicleModel->findById($id);
            $data["image_url"] = $existing["ImageURL"] ?? null;
        }

        if ($this->vehicleModel->update($id, $data)) {
            return ["success" => true];
        }

        // Cleanup uploaded file on DB failure
        if ($newImagePath) {
            deleteVehicleImage($newImagePath);
        }

        return ["success" => false, "errors" => ["form" => "Update failed."]];
    }

    public function toggleAvailability(int $id): bool
    {
        return $this->vehicleModel->toggleAvailability($id);
    }

    public function deleteVehicle(int $id): bool
    {
        // Delete the image file from disk before removing the DB record
        $vehicle = $this->vehicleModel->findById($id);
        if ($vehicle && !empty($vehicle["ImageURL"])) {
            deleteVehicleImage($vehicle["ImageURL"]);
        }

        return $this->vehicleModel->delete($id);
    }

    public function search(array $filters): array
    {
        return $this->vehicleModel->search($filters);
    }

    private function validateVehicle(array $d): array
    {
        $errors = [];
        if (empty($d["name"])) {
            $errors["name"] = "Name is required.";
        }
        if (empty($d["category"])) {
            $errors["category"] = "Category is required.";
        }

        $transmission = $d["transmission"] ?? "";
        if (!in_array($transmission, ["Manual", "Automatic"])) {
            $errors["transmission"] =
                "Transmission must be Manual or Automatic.";
        }

        if (
            !isset($d["daily_rate"]) ||
            !is_numeric($d["daily_rate"]) ||
            $d["daily_rate"] <= 0
        ) {
            $errors["daily_rate"] = "Invalid daily rate.";
        }

        return $errors;
    }
}
