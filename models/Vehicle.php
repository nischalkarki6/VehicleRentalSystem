<?php
class Vehicle
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all vehicles
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM Vehicles ORDER BY VehicleID DESC",
        );
        return $stmt->fetchAll();
    }

    /**
     * Get available vehicles
     */
    public function getAvailable(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM Vehicles WHERE IsAvailable = 1 ORDER BY Name ASC",
        );
        return $stmt->fetchAll();
    }

    /**
     * Get vehicles by category
     */
    public function getByCategory(string $category): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Vehicles WHERE Category = ? AND IsAvailable = 1",
        );
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Vehicles WHERE VehicleID = ?",
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Search vehicles
     */
    public function search(array $filters): array
    {
        $sql = "SELECT * FROM Vehicles WHERE IsAvailable = 1";
        $params = [];

        if (!empty($filters["category"])) {
            $sql .= " AND Category = ?";
            $params[] = $filters["category"];
        }

        if (!empty($filters["type"])) {
            $sql .= " AND Type LIKE ?";
            $params[] = "%" . $filters["type"] . "%";
        }

        if (!empty($filters["transmission"])) {
            $sql .= " AND Transmission = ?";
            $params[] = $filters["transmission"];
        }

        if (!empty($filters["fuel_type"])) {
            $sql .= " AND FuelType = ?";
            $params[] = $filters["fuel_type"];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Insert new vehicle
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Vehicles (Name, Category, Type, Transmission, FuelType, EngineCC, DailyRate, ImageURL, IsAvailable)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        );
        return $stmt->execute([
            $data["name"],
            $data["category"],
            $data["type"] ?? null,
            $data["transmission"],
            $data["fuel_type"],
            !empty($data["engine_cc"]) ? (int) $data["engine_cc"] : null,
            (float) $data["daily_rate"],
            !empty($data["image_url"]) ? $data["image_url"] : null,
            $data["is_available"] ?? 1,
        ]);
    }

    /**
     * Update vehicle
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Vehicles SET Name=?, Category=?, Type=?, Transmission=?, FuelType=?, EngineCC=?, DailyRate=?, ImageURL=?
             WHERE VehicleID=?",
        );
        return $stmt->execute([
            $data["name"],
            $data["category"],
            $data["type"] ?? null,
            $data["transmission"],
            $data["fuel_type"],
            !empty($data["engine_cc"]) ? (int) $data["engine_cc"] : null,
            (float) $data["daily_rate"],
            !empty($data["image_url"]) ? $data["image_url"] : null,
            $id,
        ]);
    }

    /**
     * Toggle availability
     */
    public function toggleAvailability(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Vehicles SET IsAvailable = NOT IsAvailable WHERE VehicleID = ?",
        );
        return $stmt->execute([$id]);
    }

    /**
     * Set availability explicitly (used by booking status sync)
     */
    public function setAvailability(int $id, bool $available): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Vehicles SET IsAvailable = ? WHERE VehicleID = ?",
        );
        return $stmt->execute([$available ? 1 : 0, $id]);
    }

    /**
     * Delete vehicle
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Vehicles WHERE VehicleID = ?");
        return $stmt->execute([$id]);
    }
}
