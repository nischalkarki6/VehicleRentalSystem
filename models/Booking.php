<?php
// -- Booking Model -----------------------------------------------------------
// Handles: CRUD operations for Rentals table
// -----------------------------------------------------------------------------

class Booking
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create new rental record
     */
    public function create(array $data): string|bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Rentals (UserID, VehicleID, StartDate, EndDate, PickupLoc, DropoffLoc, TotalCost, Status, TransactionUUID, PaymentStatus)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, 'Unpaid')",
        );
        $success = $stmt->execute([
            $data["user_id"],
            $data["vehicle_id"],
            $data["start_date"],
            $data["end_date"],
            $data["pickup_loc"],
            $data["dropoff_loc"],
            $data["total_cost"],
            $data["transaction_uuid"] ?? null,
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Get bookings by User ID
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, v.Name AS VehicleName, v.Category, v.ImageURL
             FROM Rentals r
             JOIN Vehicles v ON r.VehicleID = v.VehicleID
             WHERE r.UserID = ?
             ORDER BY r.RentalID DESC",
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all bookings (Admin)
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT r.*, u.FullName AS UserName, u.Email AS UserEmail, v.Name AS VehicleName, v.Category
             FROM Rentals r
             JOIN Users u ON r.UserID = u.UserID
             JOIN Vehicles v ON r.VehicleID = v.VehicleID
             ORDER BY r.RentalID DESC",
        );
        return $stmt->fetchAll();
    }

    /**
     * Update status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Rentals SET Status = ? WHERE RentalID = ?",
        );
        return $stmt->execute([$status, $id]);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Rentals WHERE RentalID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Delete booking
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Rentals WHERE RentalID = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Find by Transaction UUID
     */
    public function findByTransactionUUID(string $uuid): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Rentals WHERE TransactionUUID = ?");
        $stmt->execute([$uuid]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update payment information
     */
    public function updatePaymentInfo(int $id, string $paymentStatus, ?string $refId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Rentals SET PaymentStatus = ?, ReferenceID = ? WHERE RentalID = ?"
        );
        return $stmt->execute([$paymentStatus, $refId, $id]);
    }
}
