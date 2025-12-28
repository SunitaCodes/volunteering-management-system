-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS VolunteerManagementSystemDb;
USE VolunteerManagementSystemDb;

-- 1. Table for all users (Admins and Volunteers)
CREATE TABLE users (
    UserId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    UserName VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('admin', 'volunteer') DEFAULT 'volunteer' NOT NULL,
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table for Volunteer Events/Opportunities
CREATE TABLE events (
    EventId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(100) NOT NULL,
    Description TEXT,
    Date DATE NOT NULL,
    StartTime TIME,
    EndTime TIME,
    RequiredVolunteers INT DEFAULT 1,
    Location VARCHAR(150),
    IsFeatured BOOLEAN NOT NULL DEFAULT 0,
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table to track which volunteer signed up for which event
CREATE TABLE volunteer_signups (
    SignupId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    UserId INT NOT NULL,
    EventId INT NOT NULL,
    SignupDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE,
    FOREIGN KEY (EventId) REFERENCES events(EventId) ON DELETE CASCADE,
    UNIQUE KEY (UserId, EventId)
);

-- OPTIONAL: Insert an initial Admin user (Password is 'password')
INSERT INTO users (UserName, Email, Password, Role) VALUES (
    'adminsunita', 
    'adminsunita@vms.com', 
    '$2y$10$G3XM.IK9LOBZQx0n/Hc3we.lpjJ7z6Fkyw83HwszPQCpP8Vltml5y', -- Hashed 'adminsunita'
    'admin'
);

INSERT INTO users (UserName, Email, Password, Role) VALUES (
    'adminpratima', 
    'adminpratima@vms.com', 
    '$2y$10$u8A9Wa6B9yYfav8wO2GPmOfQ6SMIv/eitg502acwR6sxi5Bg2Bjgi', -- Hashed 'adminpratima'
    'admin'
);


-- Table: charities
CREATE TABLE charities (
    CharityId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL UNIQUE,
    Address VARCHAR(255),
    Description TEXT
);

-- Table: donations

);

-- OPTIONAL: Insert a few sample charities for testing
INSERT INTO charities (Name, Address, Description) VALUES
('Local Food Bank', '123 Main St, Anytown', 'A local organization dedicated to fighting food insecurity.'),
('Animal Shelter', '456 Oak Ave, Anytown', 'Rescuing and rehoming abandoned pets in the community.'),
('Youth Mentorship Program', '789 Pine Ln, Anytown', 'Providing educational support and mentorship for high school students.');