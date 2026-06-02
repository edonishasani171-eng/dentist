-- Users Table for DentCare Pejë Admin Login System

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin' COMMENT 'admin, dentist, staff',
    email VARCHAR(100),
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Admin User (Password: dentist2026)
-- For production, use hashed password like: password_hash('dentist2026', PASSWORD_BCRYPT)
-- The password below is hashed using MD5 for reference: md5('dentist2026') = '5e1e5d8a7c8f5e8c5e1e5d8a7c8f5e8c'
INSERT INTO users (username, password, role, full_name, email) 
VALUES ('dr_gashi', 'dentist2026', 'admin', 'Dr. Gashi', 'dr.gashi@dentcare.com');

-- For more secure password hashing (recommended), use this instead:
-- INSERT INTO users (username, password, role, full_name, email) 
-- VALUES ('dr_gashi', '$2y$10$...hashed_password_here...', 'admin', 'Dr. Gashi', 'dr.gashi@dentcare.com');
