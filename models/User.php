<?php
// ── User Model ──────────────────────────────────────────────────────────────
// Handles: CRUD operations for Users table
// ─────────────────────────────────────────────────────────────────────────────

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create new user
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Users (FullName, Email, PhoneNumber, Password, Address, Role)
             VALUES (?, ?, ?, ?, ?, ?)",
        );
        return $stmt->execute([
            $data["fullname"],
            strtolower(trim($data["email"])),
            $data["phone"],
            $data["password"], // Already hashed
            $data["address"] ?? null,
            $data["role"] ?? "user",
        ]);
    }

    /**
     * Update user profile
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Users SET FullName = ?, PhoneNumber = ?, Address = ? WHERE UserID = ?",
        );
        return $stmt->execute([
            $data["fullname"],
            $data["phone"],
            $data["address"],
            $id,
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Users SET Password = ? WHERE UserID = ?",
        );
        return $stmt->execute([$hashedPassword, $id]);
    }

    /**
     * Get all users (admin only)
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM Users ORDER BY UserID DESC");
        return $stmt->fetchAll();
    }
}
