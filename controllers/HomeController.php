<?php
require_once __DIR__ . "/../config/config.php";

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get platform statistics for the homepage display
     */
    public function getHomePageStats(): array
    {
        // Real vehicle count from Database
        $stmt_v = $this->pdo->query(
            "SELECT COUNT(*) FROM Vehicles WHERE IsAvailable = 1",
        );
        $realVehicleCount = $stmt_v->fetchColumn();

        // Real customer count from Database
        $stmt_u = $this->pdo->query(
            "SELECT COUNT(*) FROM Users WHERE Role = 'user'",
        );
        $realCustomerCount = $stmt_u->fetchColumn();

        // If the database is empty, fall back to default marketing numbers
        return [
            "vehicles" => $realVehicleCount > 0 ? $realVehicleCount : 2000,
            "customers" => $realCustomerCount > 0 ? $realCustomerCount : 5000,
            "drivers" => 3000, // Static marketing number
            "years" => 15, // Static experience years
        ];
    }
}
