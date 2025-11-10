CREATE DATABASE katpaal_dashboard;
USE katpaal_dashboard;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dashboards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE shortcuts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dashboard_id) REFERENCES dashboards(id) ON DELETE CASCADE
);

-- ڈیفالٹ ایڈمن یوزر (پاسورڈ: admin123)
INSERT INTO users (username, email, password) VALUES 
('admin', 'admin@katpaal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ڈیفالٹ ڈیٹا
INSERT INTO dashboards (user_id, name) VALUES 
(1, 'Home'),
(1, 'Social');

INSERT INTO shortcuts (dashboard_id, name, url, sort_order) VALUES 
(1, 'Google', 'https://www.google.com', 1),
(1, 'YouTube', 'https://www.youtube.com', 2),
(2, 'Twitter', 'https://twitter.com', 1),
(2, 'LinkedIn', 'https://www.linkedin.com', 2);