-- Canteen Pre-Order and Queue System Database Schema
-- Database: canteen_db

CREATE DATABASE IF NOT EXISTS canteen_db;
USE canteen_db;

CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'kitchen'
);

INSERT INTO staff (username, password_hash, role) VALUES 
('kitchen', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lR5.J1iK7hW5E2t3e6mF7zR8m5xGm', 'kitchen'),
('manager', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lR5.J1iK7hW5E2t3e6mF7zR8m5xGm', 'manager');

-- 1. Table: items
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    is_available TINYINT(1) DEFAULT 1
);

-- 2. Table: orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    total_amount DECIMAL(8, 2) NOT NULL,
    status ENUM('Received', 'Preparing', 'Ready') DEFAULT 'Received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table: order_items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Seed Data: Sample Canteen Menu Items (Food & Beverages)
INSERT INTO items (name, category, price, is_available) VALUES
('Veg Burger', 'Food', 50.00, 1),
('Chicken Sandwich', 'Food', 80.00, 1),
('Hot Samosa (2 pcs)', 'Food', 25.00, 1),
('Masala Dosa', 'Food', 60.00, 1),
('Cold Coffee', 'Beverages', 40.00, 1),
('Lemon Iced Tea', 'Beverages', 30.00, 1);
