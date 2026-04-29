<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../models/Vehicle.php";

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

    public function addVehicle(array $data): array
    {
        $errors = $this->validateVehicle($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        if ($this->vehicleModel->create($data)) {
            return ["success" => true];
        }

        return [
            "success" => false,
            "errors" => ["form" => "Failed to add vehicle."],
        ];
    }

    public function updateVehicle(int $id, array $data): array
    {
        $errors = $this->validateVehicle($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        if ($this->vehicleModel->update($id, $data)) {
            return ["success" => true];
        }

        return ["success" => false, "errors" => ["form" => "Update failed."]];
    }

    public function toggleAvailability(int $id): bool
    {
        return $this->vehicleModel->toggleAvailability($id);
    }

    public function deleteVehicle(int $id): bool
    {
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

        $fuelType = $d["fuel_type"] ?? "";
        if (!in_array($fuelType, ["Petrol", "Diesel", "Electric"])) {
            $errors["fuel_type"] =
                "Fuel type must be Petrol, Diesel, or Electric.";
        }

        if (
            !isset($d["daily_rate"]) ||
            !is_numeric($d["daily_rate"]) ||
            $d["daily_rate"] <= 0
        ) {
            $errors["daily_rate"] = "Invalid daily rate.";
        }

        // Validate engine_cc if provided (must be positive integer)
        if (
            !empty($d["engine_cc"]) &&
            (!is_numeric($d["engine_cc"]) || (int) $d["engine_cc"] <= 0)
        ) {
            $errors["engine_cc"] = "Engine CC must be a positive number.";
        }

        return $errors;
    }
}
