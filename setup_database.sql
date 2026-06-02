-- DentCare Pejë Database Setup
-- Run this SQL script in phpMyAdmin or MySQL command line to set up the database

-- Create the database
CREATE DATABASE IF NOT EXISTS dentist_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dentist_db;

-- Create appointments table with all required columns
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    service VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'Pending' COMMENT 'Pending or Confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slot (date, time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If you already have the table, run these ALTER statements to add missing columns:
-- ALTER TABLE appointments ADD COLUMN email VARCHAR(100) AFTER patient;
-- ALTER TABLE appointments ADD COLUMN notes TEXT AFTER time;
