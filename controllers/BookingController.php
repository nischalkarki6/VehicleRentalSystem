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
        $this->completeExpiredRentals();
    }

    public function create(int $userId, array $data): array
    {
        if (($_SESSION["role"] ?? "") === "admin") {
            return [
                "success" => false,
                "errors" => ["form" => "Admins can view fleet details only. Booking is available for customer accounts."],
            ];
        }

        $errors = $this->validateBooking($data);
        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        $data["pickup_loc"] = trim($data["pickup_loc"] ?? "");
        $data["dropoff_loc"] = trim($data["dropoff_loc"] ?? "");

        $vehicleId = (int) $data["vehicle_id"];
        $start = $this->parseBookingDate($data["start_date"]);
        $end = $this->parseBookingDate($data["end_date"]);

        try {
            $this->pdo->beginTransaction();

            // Lock the vehicle row while checking availability and date conflicts.
            $vehicle = $this->vehicleModel->findByIdForUpdate($vehicleId);
            if (!$vehicle) {
                $this->pdo->rollBack();
                return [
                    "success" => false,
                    "errors" => ["form" => "Vehicle not found."],
                ];
            }
            if (!$vehicle["IsAvailable"]) {
                $this->pdo->rollBack();
                return [
                    "success" => false,
                    "errors" => ["form" => "This vehicle is no longer available."],
                ];
            }

            $this->bookingModel->deleteOverlappingUnpaidOnlineAttemptsForUser(
                $userId,
                $vehicleId,
                $data["start_date"],
                $data["end_date"]
            );

            if ($this->bookingModel->hasUnavailableBookingForVehicle($vehicleId)) {
                $this->pdo->rollBack();
                return [
                    "success" => false,
                    "errors" => ["form" => "This vehicle is no longer available."],
                ];
            }

            if ($this->bookingModel->hasOverlappingBooking($vehicleId, $data["start_date"], $data["end_date"])) {
                $this->pdo->rollBack();
                return [
                    "success" => false,
                    "errors" => ["form" => "This vehicle is already booked for the selected dates."],
                ];
            }

            // Apply dynamic pricing multiplier
            $multiplier = $this->vehicleModel->calculateCategoryMultiplier($vehicle["Category"]);
            $dynamicRate = round((float) $vehicle["DailyRate"] * $multiplier);

            // Compute cost server-side
            $days = max(1, (int) $start->diff($end)->days);
            $insuranceFee = $dynamicRate > 4500 ? 3300 : 0;
            $totalCost = ($days * $dynamicRate) + $insuranceFee;

            // Always force online payment; never trust submitted payment data.
            $data["payment_method"] = "Online";
            $data["user_id"] = $userId;
            $data["total_cost"] = $totalCost;
            $data["transaction_uuid"] = $this->generateTransactionUuid();

            $rentalId = $this->bookingModel->create($data);
            if (!$rentalId) {
                $this->pdo->rollBack();
                return ["success" => false, "errors" => ["form" => "Booking failed. Please try again."]];
            }

            $this->pdo->commit();
            $_SESSION['esewa_rental_id'] = $rentalId;
            return ["success" => true, "rental_id" => $rentalId];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[BookingController::create] " . $e->getMessage());
        }

        return ["success" => false, "errors" => ["form" => "Booking failed. Please try again."]];
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
        $allowed = ["Confirmed", "Active", "Completed", "Cancelled"];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            return false;
        }

        if ($status === "Active" && ($booking["PaymentStatus"] ?? "") !== "Paid") {
            return false;
        }

        $result = $this->bookingModel->updateStatus($id, $status);
        if (!$result) {
            return false;
        }

        // Sync vehicle availability with booking lifecycle.
        if ($status === "Confirmed" || $status === "Active") {
            $this->vehicleModel->setAvailability($booking["VehicleID"], false);
        } elseif (
            ($status === "Completed" || $status === "Cancelled") &&
            in_array($booking["Status"], ["Confirmed", "Active"], true) &&
            !$this->bookingModel->hasUnavailableBookingForVehicle((int) $booking["VehicleID"], $id)
        ) {
            $this->vehicleModel->setAvailability($booking["VehicleID"], true);
        }

        return true;
    }

    public function updateAdminEditableBooking(int $id, array $data): array
    {
        $booking = $this->bookingModel->findById($id);
        if (!$booking || !$this->canAdminModifyBooking($booking)) {
            return [
                "success" => false,
                "error" => "Only unpaid or pending bookings can be edited.",
            ];
        }

        $errors = $this->validateBooking($data);
        if (!empty($errors)) {
            return [
                "success" => false,
                "error" => reset($errors) ?: "Please check the booking details.",
            ];
        }

        $vehicleId = (int) $data["vehicle_id"];
        $start = $this->parseBookingDate($data["start_date"]);
        $end = $this->parseBookingDate($data["end_date"]);

        try {
            $this->pdo->beginTransaction();

            $vehicle = $this->vehicleModel->findByIdForUpdate($vehicleId);
            if (!$vehicle) {
                $this->pdo->rollBack();
                return ["success" => false, "error" => "Vehicle not found."];
            }

            if ($this->bookingModel->hasOverlappingBooking(
                $vehicleId,
                $data["start_date"],
                $data["end_date"],
                $id
            )) {
                $this->pdo->rollBack();
                return [
                    "success" => false,
                    "error" => "This vehicle already has a booking for the selected dates.",
                ];
            }

            $multiplier = $this->vehicleModel->calculateCategoryMultiplier($vehicle["Category"]);
            $dynamicRate = round((float) $vehicle["DailyRate"] * $multiplier);
            $days = max(1, (int) $start->diff($end)->days);
            $insuranceFee = $dynamicRate > 4500 ? 3300 : 0;

            $updated = $this->bookingModel->updateAdminEditableBooking($id, [
                "vehicle_id" => $vehicleId,
                "start_date" => $data["start_date"],
                "end_date" => $data["end_date"],
                "pickup_loc" => trim($data["pickup_loc"] ?? ""),
                "dropoff_loc" => trim($data["dropoff_loc"] ?? ""),
                "total_cost" => ($days * $dynamicRate) + $insuranceFee,
            ]);

            if (!$updated) {
                $this->pdo->rollBack();
                return ["success" => false, "error" => "Booking update failed."];
            }

            $this->pdo->commit();
            return ["success" => true];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[BookingController::updateAdminEditableBooking] " . $e->getMessage());
            return ["success" => false, "error" => "Booking update failed."];
        }
    }

    public function deleteAdminEditableBooking(int $id): bool
    {
        $booking = $this->bookingModel->findById($id);
        if (!$booking || !$this->canAdminModifyBooking($booking)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $deleted = $this->bookingModel->delete($id);

            if (!$deleted) {
                $this->pdo->rollBack();
                return false;
            }

            if (
                in_array($booking["Status"], ["Confirmed", "Active"], true) &&
                !$this->bookingModel->hasUnavailableBookingForVehicle((int) $booking["VehicleID"], $id)
            ) {
                $this->vehicleModel->setAvailability((int) $booking["VehicleID"], true);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[BookingController::deleteAdminEditableBooking] " . $e->getMessage());
            return false;
        }
    }

    public function startPaymentRetry(int $id, int $userId): array
    {
        $booking = $this->bookingModel->findById($id);
        if (
            !$booking ||
            (int) $booking["UserID"] !== $userId ||
            strtolower(trim((string) ($booking["PaymentStatus"] ?? ""))) !== "unpaid" ||
            strtolower(trim((string) ($booking["Status"] ?? ""))) !== "pending"
        ) {
            return [
                "success" => false,
                "error" => "This booking cannot be paid again.",
            ];
        }

        $transactionUuid = $this->generateTransactionUuid();
        if (!$this->bookingModel->updateTransactionUuid($id, $transactionUuid)) {
            return [
                "success" => false,
                "error" => "Unable to restart payment. Please try again.",
            ];
        }

        $_SESSION["esewa_rental_id"] = $id;
        return ["success" => true];
    }

    public function completeExpiredRentals(): int
    {
        $today = (new DateTimeImmutable("today"))->format("Y-m-d");

        try {
            $this->pdo->beginTransaction();
            $vehicleIds = $this->bookingModel->completeExpiredActiveRentals($today);

            foreach ($vehicleIds as $vehicleId) {
                if (!$this->bookingModel->hasUnavailableBookingForVehicle((int) $vehicleId)) {
                    $this->vehicleModel->setAvailability((int) $vehicleId, true);
                }
            }

            $this->pdo->commit();
            return count($vehicleIds);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[BookingController::completeExpiredRentals] " . $e->getMessage());
            return 0;
        }
    }

    private function validateBooking(array $d): array
    {
        $errors = [];
        if (empty($d["vehicle_id"])) {
            $errors["vehicle_id"] = "Vehicle is required.";
        }

        $start = null;
        if (empty($d["start_date"])) {
            $errors["start_date"] = "Start date is required.";
        } else {
            $start = $this->parseBookingDate((string) $d["start_date"]);
            $today = new DateTimeImmutable("today");
            if (!$start) {
                $errors["start_date"] = "Start date is invalid.";
            } elseif ($start < $today) {
                $errors["start_date"] = "Start date cannot be in the past.";
            }
        }

        if (empty($d["end_date"])) {
            $errors["end_date"] = "End date is required.";
        } else {
            $end = $this->parseBookingDate((string) $d["end_date"]);
            if (!$end) {
                $errors["end_date"] = "End date is invalid.";
            } elseif ($start && $end <= $start) {
                $errors["end_date"] = "End date must be after start date.";
            }
        }

        if (empty(trim($d["pickup_loc"] ?? ""))) {
            $errors["pickup_loc"] = "Pickup location is required.";
        }
        if (empty(trim($d["dropoff_loc"] ?? ""))) {
            $errors["dropoff_loc"] = "Destination is required.";
        }

        return $errors;
    }

    private function parseBookingDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $hasErrors = is_array($dateErrors) &&
            ($dateErrors["warning_count"] > 0 || $dateErrors["error_count"] > 0);

        if (!$date || $hasErrors || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    private function canAdminModifyBooking(array $booking): bool
    {
        $status = strtolower(trim((string) ($booking["Status"] ?? "")));
        $paymentStatus = strtolower(trim((string) ($booking["PaymentStatus"] ?? "")));

        return $status === "pending" || $paymentStatus === "unpaid";
    }

    private function generateTransactionUuid(): string
    {
        return "DE-" . date("ymdHis") . "-" . bin2hex(random_bytes(8));
    }
}
