<?php
// User model: CRUD, email verification, and password reset token management.

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
     * Create new user (unverified users require email confirmation)
     * Stores a SHA-256 hash of the verification token, never the raw token.
     *
     * @param array  $data      Registration fields
     * @param string $tokenHash SHA-256 hash of the raw verification token
     * @return bool
     */
    public function create(array $data, string $tokenHash = ''): bool
    {
        $role = $data["role"] ?? "user";
        $isVerified = $role === "admin" ? 1 : 0;
        $verificationHash = $isVerified ? null : ($tokenHash ?: null);
        $verificationExpiry = $isVerified ? null : date("Y-m-d H:i:s", time() + 3600);

        $stmt = $this->db->prepare(
            "INSERT INTO Users (FullName, Email, PhoneNumber, Password, Address, Role,
                                IsVerified, VerificationTokenHash, VerificationExpiry)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        );
        return $stmt->execute([
            $data["fullname"],
            strtolower(trim($data["email"])),
            $data["phone"],
            $data["password"], // Already hashed
            $data["address"] ?? null,
            $role,
            $isVerified,
            $verificationHash,
            $verificationExpiry,
        ]);
    }

    /**
     * Find a user by their verification OTP hash (must be unexpired).
     */
    public function findByVerificationToken(string $tokenHash, string $email = ''): ?array
    {
        $emailFilter = $email !== '' ? "AND Email = ?" : "";
        $stmt = $this->db->prepare(
            "SELECT * FROM Users
             WHERE VerificationTokenHash = ?
               AND VerificationExpiry > NOW()
               {$emailFilter}
               AND IsVerified = 0"
        );
        $params = [$tokenHash];
        if ($email !== '') {
            $params[] = strtolower(trim($email));
        }
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Mark a user as verified and clear the verification token.
     */
    public function markVerified(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Users
             SET IsVerified = 1,
                 VerificationTokenHash = NULL,
                 VerificationExpiry = NULL
             WHERE UserID = ?"
        );
        return $stmt->execute([$userId]);
    }

    /**
     * Refresh the verification token (for resend functionality).
     */
    public function updateVerificationToken(int $userId, string $tokenHash): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Users
             SET VerificationTokenHash = ?,
                 VerificationExpiry = DATE_ADD(NOW(), INTERVAL 60 MINUTE)
             WHERE UserID = ?"
        );
        return $stmt->execute([$tokenHash, $userId]);
    }

    /**
     * Store a password-reset token hash in the PasswordResets table.
     * Invalidates any previous unused tokens for this user.
     */
    public function createPasswordReset(int $userId, string $tokenHash): bool
    {
        // Invalidate old tokens
        $del = $this->db->prepare(
            "DELETE FROM PasswordResets WHERE UserID = ?"
        );
        $del->execute([$userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO PasswordResets (UserID, TokenHash, ExpiresAt)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE))"
        );
        return $stmt->execute([$userId, $tokenHash]);
    }

    /**
     * Find a valid (unused, unexpired) password-reset record by token hash.
     * Returns the reset row joined with user data.
     */
    public function findPasswordReset(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pr.*, u.Email, u.FullName, u.UserID
             FROM PasswordResets pr
             JOIN Users u ON u.UserID = pr.UserID
             WHERE pr.TokenHash = ?
               AND pr.ExpiresAt > NOW()
               AND pr.Used = 0"
        );
        $stmt->execute([$tokenHash]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Consume a reset token (mark used) and update the user's password.
     */
    public function resetPassword(int $resetId, int $userId, string $hashedPassword): bool
    {
        $this->db->beginTransaction();
        try {
            // Update password
            $pw = $this->db->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
            $pw->execute([$hashedPassword, $userId]);

            // Delete the used token
            $del = $this->db->prepare("DELETE FROM PasswordResets WHERE ResetID = ?");
            $del->execute([$resetId]);

            // Delete any other tokens for this user
            $cleanup = $this->db->prepare("DELETE FROM PasswordResets WHERE UserID = ?");
            $cleanup->execute([$userId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('[User::resetPassword] ' . $e->getMessage());
            return false;
        }
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
