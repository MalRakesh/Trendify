-- schema.sql - Trendify Database Schema
-- Version: 1.0
-- Author: Backend Bhai
-- Total Lines: ~320

-- Create Database
CREATE DATABASE IF NOT EXISTS trendify_db;
USE trendify_db;

-- Users Table
-- Stores customer, dealer, and admin users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'dealer', 'admin') DEFAULT 'customer',
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Categories Table
-- Product categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug)
);

-- Products Table
-- Stores all products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    image VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    FULLTEXT idx_search (name, description)
);

-- Orders Table
-- Stores customer orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    contact_phone VARCHAR(15) NOT NULL,
    payment_method ENUM('cod', 'online') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number)
);

-- Order Items Table
-- Links products to orders
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

-- Cart Table
-- User's active cart
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user (user_id)
);

-- Reviews Table
-- Customer reviews for products
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, product_id),
    INDEX idx_product (product_id),
    INDEX idx_rating (rating)
);

-- Dealer Products Table (Optional)
-- Links dealers to products they sell
CREATE TABLE dealer_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_id INT NOT NULL,
    product_id INT NOT NULL,
    commission_rate DECIMAL(5, 2) DEFAULT 10.00,
    
    FOREIGN KEY (dealer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dealer_product (dealer_id, product_id)
);

-- Logs Table
-- System activity logs
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_date (created_at)
);

-- Insert Sample Data

-- Categories
INSERT INTO categories (name, slug, description) VALUES 
('Men', 'men', 'Clothing and accessories for men'),
('Women', 'women', 'Clothing and accessories for women'),
('Accessories', 'accessories', 'Fashion accessories'),
('Footwear', 'footwear', 'Shoes and sandals'),
('Electronics', 'electronics', 'Electronic gadgets');

-- Users
INSERT INTO users (name, email, password, role) VALUES 
('Admin Kumar', 'admin@trendify.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Dealer Singh', 'dealer@trendify.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dealer'),
('Regular Customer', 'user@trendify.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Products
INSERT INTO products (name, description, price, category_id, image, stock, featured) VALUES 
('Casual Men''s Shirt', 'Stylish cotton shirt for casual wear', 899.00, 1, 'assets/product1.jpg', 50, 1),
('Women''s Denim Jacket', 'Trendy denim jacket with modern fit', 1499.00, 2, 'assets/product2.jpg', 30, 1),
('Leather Wallet', 'Genuine leather wallet with RFID protection', 599.00, 3, 'assets/product3.jpg', 100, 0),
('Running Shoes', 'Lightweight running shoes with shock absorption', 1999.00, 4, 'assets/product4.jpg', 40, 1),
('Wireless Earbuds', 'Bluetooth 5.0 earbuds with 20hr battery', 1299.00, 5, 'assets/product5.jpg', 60, 1);

-- Orders
INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, contact_phone) VALUES 
('ORD-1001', 3, 2498.00, 'delivered', '123 Main St, Delhi', '9876543210'),
('ORD-1002', 3, 1499.00, 'shipped', '456 Oak Ave, Mumbai', '8765432109');

-- Order Items
INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) VALUES 
(1, 1, 1, 899.00, 899.00),
(1, 3, 1, 599.00, 599.00),
(1, 8, 1, 799.00, 799.00),
(2, 2, 1, 1499.00, 1499.00);

-- Reviews
INSERT INTO reviews (user_id, product_id, rating, comment) VALUES 
(3, 1, 5, 'Great fit and quality!'),
(3, 2, 4, 'Love the style!'),
(3, 4, 5, 'Perfect for jogging.');

-- End of schema.sql