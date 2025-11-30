CREATE DATABASE ticketing;
USE ticketing;

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    department_id INT,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255),
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Replies (admin + user can reply later)
CREATE TABLE ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT,
    is_admin TINYINT(1) DEFAULT 0, -- 1 = admin, 0 = user
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

-- Insert sample departments
INSERT INTO departments (name) VALUES 
('Technical Support'), ('Billing'), ('Sales'), ('General Inquiry');

-- Simple admin (username: admin, password: admin123)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255) -- hashed
);
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$j3ZQ7i3eO5Y8g6v2x9k5au5Y8g6v2x9k5au5Y8g6v2x9k5au5Y8g6'); -- password: admin123