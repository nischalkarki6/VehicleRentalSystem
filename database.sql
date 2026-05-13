CREATE DATABASE IF NOT EXISTS `vrs`;
USE `vrs`;

-- =============================================
-- 1. Users Table
-- =============================================
CREATE TABLE IF NOT EXISTS `Users` (
    `UserID`      INT AUTO_INCREMENT PRIMARY KEY,
    `FullName`    VARCHAR(100) NOT NULL,
    `Email`       VARCHAR(100) NOT NULL UNIQUE,
    `PhoneNumber` VARCHAR(20)  NOT NULL,
    `Password`    VARCHAR(255) NOT NULL,
    `Address`     TEXT,
    `Role`        ENUM('user', 'admin') DEFAULT 'user',
    `IsVerified`             TINYINT(1)   NOT NULL DEFAULT 0,
    `VerificationTokenHash`  VARCHAR(64)  DEFAULT NULL,
    `VerificationExpiry`     DATETIME     DEFAULT NULL,
    `DateJoined`  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 2. Vehicles Table
-- =============================================
CREATE TABLE IF NOT EXISTS `Vehicles` (
    `VehicleID`    INT AUTO_INCREMENT PRIMARY KEY,
    `Name`         VARCHAR(100)   NOT NULL,
    `Category`     VARCHAR(50)    NOT NULL,
    `Type`         VARCHAR(50),
    `Transmission` VARCHAR(20)    NOT NULL,
    `DailyRate`    DECIMAL(10,2)  NOT NULL,
    `ImageURL`     VARCHAR(255),
    `IsAvailable`  TINYINT(1) DEFAULT 1
);

-- =============================================
-- 3. Rentals Table (Bookings)
-- =============================================
CREATE TABLE IF NOT EXISTS `Rentals` (
    `RentalID`   INT AUTO_INCREMENT PRIMARY KEY,
    `UserID`     INT NOT NULL,
    `VehicleID`  INT NOT NULL,
    `StartDate`  DATE NOT NULL,
    `EndDate`    DATE NOT NULL,
    `PickupLoc`  VARCHAR(100) NOT NULL,
    `DropoffLoc` VARCHAR(100) NOT NULL,
    `TotalCost`  DECIMAL(10,2) NOT NULL,
    `Status`     VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (`UserID`)    REFERENCES `Users`(`UserID`)       ON DELETE CASCADE,
    FOREIGN KEY (`VehicleID`) REFERENCES `Vehicles`(`VehicleID`) ON DELETE CASCADE
);

-- =============================================
-- 4. Contact Messages Table
-- =============================================
CREATE TABLE IF NOT EXISTS `ContactMessages` (
    `MessageID` INT AUTO_INCREMENT PRIMARY KEY,
    `UserName`  VARCHAR(100) NOT NULL,
    `UserEmail` VARCHAR(100) NOT NULL,
    `Message`   TEXT NOT NULL,
    `SentDate`  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 5. Password Resets Table (SHA-256 hashed tokens)
-- =============================================
CREATE TABLE IF NOT EXISTS `PasswordResets` (
    `ResetID`   INT AUTO_INCREMENT PRIMARY KEY,
    `UserID`    INT NOT NULL,
    `TokenHash` VARCHAR(64)  NOT NULL,
    `ExpiresAt` DATETIME     NOT NULL,
    `Used`      TINYINT(1)   DEFAULT 0,
    `CreatedAt` DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
    INDEX `idx_token_hash` (`TokenHash`),
    INDEX `idx_expires` (`ExpiresAt`)
);

-- =============================================
-- 6. Rate Limits Table (Brute-force Protection)
-- =============================================
CREATE TABLE IF NOT EXISTS `RateLimits` (
    `ID`          INT AUTO_INCREMENT PRIMARY KEY,
    `IPAddress`   VARCHAR(45)  NOT NULL,
    `Action`      VARCHAR(50)  NOT NULL,
    `Attempts`    INT          NOT NULL DEFAULT 1,
    `WindowStart` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_action` (`IPAddress`, `Action`)
);

-- =============================================
-- Default Admin Account (pre-verified)
-- Email: admin@admin.com | Password: Admin123
-- =============================================
INSERT INTO `Users` (`FullName`, `Email`, `PhoneNumber`, `Password`, `Role`, `IsVerified`)
VALUES ('System Admin', 'admin@admin.com', '1234567890', '$2y$12$iShJy4B0QUdyjHYUDspOxeqKh2y82IuGxscKosXs4MX/0X.BDmY9q', 'admin', 1);
