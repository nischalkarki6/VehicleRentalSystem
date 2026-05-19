<?php

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data, string $tokenHash = ''): bool
    {
        $role = $data["role"] ?? "user";
        $isVerified = $role === "admin" ? 1 : 0;
        $verificationHash = $isVerified ? null : ($tokenHash ?: null);
        $verificationExpirySql = $isVerified ? "NULL" : "DATE_ADD(NOW(), INTERVAL 60 MINUTE)";

        $stmt = $this->db->prepare(
            "INSERT INTO Users (FullName, Email, PhoneNumber, Password, Address, Role,
                                 IsVerified, VerificationTokenHash, VerificationExpiry)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, {$verificationExpirySql})",
        );
        return $stmt->execute([
            $data["fullname"],
            strtolower(trim($data["email"])),
            $data["phone"],
            $data["password"],
            $data["address"] ?? null,
            $role,
            $isVerified,
            $verificationHash,
        ]);
    }

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

    public function createPasswordResetOtp(int $userId, string $otpHash, int $ttlMinutes = 10): bool
    {
        $ttlMinutes = max(1, min($ttlMinutes, 60));

        $del = $this->db->prepare(
            "DELETE FROM PasswordResets WHERE UserID = ?"
        );
        $del->execute([$userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO PasswordResets (UserID, TokenHash, ExpiresAt)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL {$ttlMinutes} MINUTE))"
        );
        return $stmt->execute([$userId, $otpHash]);
    }

    public function consumePasswordResetOtp(string $email, string $otp): ?array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT pr.ResetID, pr.TokenHash, pr.UserID, u.Email, u.FullName
                 FROM PasswordResets pr
                 JOIN Users u ON u.UserID = pr.UserID
                 WHERE u.Email = ?
                   AND pr.ExpiresAt > NOW()
                   AND pr.Used = 0
                 ORDER BY pr.CreatedAt DESC
                 LIMIT 1
                 FOR UPDATE"
            );
            $stmt->execute([strtolower(trim($email))]);
            $reset = $stmt->fetch() ?: null;

            if (!$reset || !password_verify($otp, $reset["TokenHash"])) {
                $this->db->rollBack();
                return null;
            }

            $cleanup = $this->db->prepare("DELETE FROM PasswordResets WHERE UserID = ?");
            $cleanup->execute([$reset["UserID"]]);

            $this->db->commit();
            return $reset;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('[User::consumePasswordResetOtp] ' . $e->getMessage());
            return null;
        }
    }

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

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Users SET Password = ? WHERE UserID = ?",
        );
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM Users ORDER BY UserID DESC");
        return $stmt->fetchAll();
    }
}
