-- Canteen Pre-Order and Queue System Database Schema
-- Database: canteen_db

CREATE DATABASE IF NOT EXISTS canteen_db;
USE canteen_db;

-- 1. Table: items
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    description VARCHAR(255),
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
INSERT INTO items (name, category, price, description, is_available) VALUES
('Veg Burger', 'Food', 50.00, 'Crispy spiced potato patty with freshly chopped lettuce & herb sauce', 1),
('Chicken Sandwich', 'Food', 80.00, 'Tender chicken fillet served with creamy mint mayonnaise in toasted bread', 1),
('Hot Samosa (2 pcs)', 'Food', 25.00, 'Traditional golden-fried pastry stuffed with spiced potatoes and peas', 1),
('Masala Dosa', 'Food', 60.00, 'Crispy golden crepe stuffed with mildly spiced potato masala, served with chutney', 1),
('Cold Coffee', 'Beverages', 40.00, 'Rich chilled espresso blended with fresh dairy milk and cocoa', 1),
('Lemon Iced Tea', 'Beverages', 30.00, 'Freshly brewed black tea infused with natural lemon juice and crushed ice', 1);
