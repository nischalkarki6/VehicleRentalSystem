<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../models/Booking.php";
require_once __DIR__ . "/../models/Vehicle.php";

class BookingController
{
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->bookingModel = new Booking($pdo);
        $this->vehicleModel = new Vehicle($pdo);
    }

    public function create(int $userId, array $data): array
    {
        $errors = $this->validateBooking($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        // Fetch vehicle from DB to get real rate (prevents tampering)
        $vehicle = $this->vehicleModel->findById((int) $data["vehicle_id"]);
        if (!$vehicle) {
            return [
                "success" => false,
                "errors" => ["form" => "Vehicle not found."],
            ];
        }
        if (!$vehicle["IsAvailable"]) {
            return [
                "success" => false,
                "errors" => ["form" => "This vehicle is no longer available."],
            ];
        }

        // Compute cost server-side
        $start = new DateTime($data["start_date"]);
        $end = new DateTime($data["end_date"]);
        $days = max(1, (int) $end->diff($start)->days);
        $totalCost = $days * $vehicle["DailyRate"];

        $data["user_id"] = $userId;
        $data["total_cost"] = $totalCost;

        $rentalId = $this->bookingModel->create($data);
        if ($rentalId) {
            return ["success" => true, "rental_id" => $rentalId];
        }

        return ["success" => false, "errors" => ["form" => "Booking failed."]];
    }

    public function getUserBookings(int $userId): array
    {
        return $this->bookingModel->getByUserId($userId);
    }

    public function getAllBookings(): array
    {
        return $this->bookingModel->getAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ["Pending", "Active", "Completed", "Cancelled"];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            return false;
        }

        $result = $this->bookingModel->updateStatus($id, $status);
        if (!$result) {
            return false;
        }

        // Sync vehicle availability
        if ($status === "Active") {
            // Approving → mark vehicle as unavailable
            $this->vehicleModel->setAvailability($booking["VehicleID"], false);
        } elseif ($status === "Completed" || $status === "Cancelled") {
            // Completing or cancelling → free the vehicle
            $this->vehicleModel->setAvailability($booking["VehicleID"], true);
        }

        return true;
    }

    private function validateBooking(array $d): array
    {
        $errors = [];
        if (empty($d["vehicle_id"])) {
            $errors["vehicle_id"] = "Vehicle is required.";
        }
        if (empty($d["start_date"])) {
            $errors["start_date"] = "Start date is required.";
        } else {
            $today = new DateTime("today");
            $start = new DateTime($d["start_date"]);
            if ($start < $today) {
                $errors["start_date"] = "Start date cannot be in the past.";
            }
        }
        if (empty($d["end_date"])) {
            $errors["end_date"] = "End date is required.";
        } elseif (
            !empty($d["start_date"]) &&
            $d["end_date"] <= $d["start_date"]
        ) {
            $errors["end_date"] = "End date must be after start date.";
        }
        if (empty($d["pickup_loc"])) {
            $errors["pickup_loc"] = "Pickup location is required.";
        }
        if (empty($d["dropoff_loc"])) {
            $errors["dropoff_loc"] = "Dropoff location is required.";
        }
        return $errors;
    }
}
