-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS VRS;
USE VRS;

-- 2. Vehicles Table: Optimized for the frontend filters
CREATE TABLE Vehicles (
    VehicleID    INT(11)        NOT NULL AUTO_INCREMENT,
    Name         VARCHAR(100)   NOT NULL, 
    Category     ENUM('Car', 'Bike') NOT NULL, 
    Type         VARCHAR(50)    NULL, -- (e.g., SUV, Hatchback, Sedan, Cruiser)
    Transmission ENUM('Manual', 'Automatic') DEFAULT 'Manual',
    FuelType     ENUM('Petrol', 'Diesel', 'Electric') DEFAULT 'Petrol',
    EngineCC     INT(11)        NULL, -- Crucial for bike filtering
    DailyRate    DECIMAL(10,2)  NOT NULL,
    ImageURL     VARCHAR(255)   NULL,
    IsAvailable  TINYINT(1)     DEFAULT 1,
    PRIMARY KEY (VehicleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Users Table: Matches the current signup form
CREATE TABLE Users (
    UserID       INT(11)        NOT NULL AUTO_INCREMENT,
    FullName     VARCHAR(100)   NOT NULL,
    Email        VARCHAR(100)   NOT NULL,
    PhoneNumber  VARCHAR(20)    NOT NULL,
    Password     VARCHAR(255)   NOT NULL, -- Salted and hashed
    DateJoined   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID),
    UNIQUE KEY uq_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Rentals Table: Tracks bookings with locations
CREATE TABLE Rentals (
    RentalID     INT(11)        NOT NULL AUTO_INCREMENT,
    UserID       INT(11)        NOT NULL,
    VehicleID    INT(11)        NOT NULL,
    StartDate    DATE           NOT NULL,
    EndDate      DATE           NULL,
    PickupLoc    VARCHAR(100)   NOT NULL,
    DropoffLoc   VARCHAR(100)   NOT NULL,
    TotalCost    DECIMAL(10,2)  NULL,
    Status       ENUM('Pending', 'Active', 'Completed', 'Cancelled') DEFAULT 'Pending',
    PRIMARY KEY (RentalID),
    FOREIGN KEY (UserID)    REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (VehicleID) REFERENCES Vehicles(VehicleID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Contact Messages Table: For the "Contact Us" form
CREATE TABLE ContactMessages (
    MessageID    INT(11)        NOT NULL AUTO_INCREMENT,
    UserName     VARCHAR(100)   NOT NULL,
    UserEmail    VARCHAR(100)   NOT NULL,
    Message      TEXT           NOT NULL,
    SentDate     DATETIME       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MessageID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
