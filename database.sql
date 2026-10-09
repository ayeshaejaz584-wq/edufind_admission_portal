CREATE DATABASE IF NOT EXISTS edufind_admission
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE edufind_admission;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id VARCHAR(30) NOT NULL UNIQUE,

    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    cnic VARCHAR(30) NOT NULL,
    dob DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    gender VARCHAR(30) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,

    qualification VARCHAR(100) NOT NULL,
    board VARCHAR(150) NOT NULL,
    roll_number VARCHAR(100) DEFAULT NULL,
    passing_year YEAR NOT NULL,
    total_marks INT NOT NULL,
    obtained_marks INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,

    university VARCHAR(200) NOT NULL,
    campus VARCHAR(200) NOT NULL,
    program VARCHAR(200) NOT NULL,
    session VARCHAR(100) DEFAULT NULL,

    academic_document VARCHAR(255) NOT NULL,
    cnic_document VARCHAR(255) NOT NULL,
    photo_document VARCHAR(255) NOT NULL,

    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cnic (cnic),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB;
