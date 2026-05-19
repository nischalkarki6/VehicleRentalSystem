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
        $paymentMethod = $data['payment_method'] ?? 'Cash';
        $stmt = $this->db->prepare(
            "INSERT INTO Rentals (UserID, VehicleID, StartDate, EndDate, PickupLoc, DropoffLoc, TotalCost, Status, TransactionUUID, PaymentMethod, PaymentStatus)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, 'Unpaid')",
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
            $paymentMethod
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
             AND r.PaymentStatus = 'Paid'
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

    public function hasOverlappingBooking(
        int $vehicleId,
        string $startDate,
        string $endDate,
        ?int $excludeRentalId = null
    ): bool {
        $sql = "
            SELECT RentalID
            FROM Rentals
            WHERE VehicleID = ?
              AND Status IN ('Pending', 'Confirmed', 'Active')
              AND StartDate <= ?
              AND EndDate >= ?
        ";
        $params = [$vehicleId, $endDate, $startDate];

        if ($excludeRentalId !== null) {
            $sql .= " AND RentalID <> ?";
            $params[] = $excludeRentalId;
        }

        $sql .= " LIMIT 1 FOR UPDATE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function hasActiveBookingForVehicle(int $vehicleId, ?int $excludeRentalId = null): bool
    {
        $sql = "
            SELECT RentalID
            FROM Rentals
            WHERE VehicleID = ?
              AND Status = 'Active'
        ";
        $params = [$vehicleId];

        if ($excludeRentalId !== null) {
            $sql .= " AND RentalID <> ?";
            $params[] = $excludeRentalId;
        }

        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function completeExpiredActiveRentals(string $today): array
    {
        $stmt = $this->db->prepare(
            "SELECT RentalID, VehicleID
             FROM Rentals
             WHERE Status = 'Active'
               AND EndDate < ?
             FOR UPDATE"
        );
        $stmt->execute([$today]);
        $expiredRentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($expiredRentals)) {
            return [];
        }

        $rentalIds = array_map('intval', array_column($expiredRentals, 'RentalID'));
        $placeholders = implode(',', array_fill(0, count($rentalIds), '?'));
        $update = $this->db->prepare(
            "UPDATE Rentals
             SET Status = 'Completed'
             WHERE RentalID IN ($placeholders)"
        );
        $update->execute($rentalIds);

        return array_values(array_unique(array_map(
            'intval',
            array_column($expiredRentals, 'VehicleID')
        )));
    }

    public function updatePaymentInfo(int $id, string $paymentStatus, ?string $refId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Rentals SET PaymentStatus = ?, ReferenceID = ? WHERE RentalID = ?"
        );
        return $stmt->execute([$paymentStatus, $refId, $id]);
    }

    /**
     * Verifies an online payment, marks it as Paid, and Confirms the booking.
     */
    public function verifyOnlinePayment(string $transactionUuid, ?string $referenceId = null): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT RentalID, Status, PaymentStatus
                FROM Rentals
                WHERE TransactionUUID = :uuid
                FOR UPDATE
            ");
            $stmt->execute([':uuid' => $transactionUuid]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception("Booking not found.");
            }

            if ($booking['PaymentStatus'] === 'Paid') {
                $this->db->rollBack();
                return true;
            }

            $updateStmt = $this->db->prepare("
                UPDATE Rentals
                SET
                    Status = 'Confirmed',
                    PaymentStatus = 'Paid',
                    ReferenceID = :refId
                WHERE TransactionUUID = :uuid
            ");

            $updateStmt->execute([
                ':refId' => $referenceId,
                ':uuid'  => $transactionUuid
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Payment verification failed: " . $e->getMessage());
            return false;
        }
    }
}
