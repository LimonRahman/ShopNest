-- ShopNest E-commerce Database Schema
-- Run this SQL file to create all necessary tables

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS shopNest;
-- USE shopNest;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) DEFAULT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(500),
    features TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample admin user
-- Password: password
-- Change this password after first login!
INSERT INTO users (name, email, phone, password, role) VALUES 
('Admin User', 'admin@shopnest.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Insert sample products (using local image filenames)
INSERT INTO products (name, description, category, price, old_price, stock, image, features) VALUES
('Wireless Headphones', 'High-quality wireless headphones with noise cancellation', 'electronics', 79.99, 99.99, 50, 'headphones.jpg', 'Noise Cancellation\n30-hour battery\nBluetooth 5.0\nComfortable fit'),
('Smart Watch', 'Feature-rich smartwatch with fitness tracking', 'electronics', 199.99, 249.99, 30, 'smartwatch.jpg', 'Fitness Tracking\nHeart Rate Monitor\nWater Resistant\nLong Battery Life'),
('Casual T-Shirt', 'Comfortable cotton t-shirt in various colors', 'clothing', 24.99, NULL, 100, 'tshirt.jpg', '100% Cotton\nMachine Washable\nMultiple Colors\nSizes S-XL'),
('Coffee Maker', 'Automatic drip coffee maker with timer', 'home', 49.99, 69.99, 25, 'coffee-maker.jpg', 'Programmable Timer\n12-Cup Capacity\nAuto Shut-off\nReusable Filter'),
('Running Shoes', 'Comfortable running shoes for all terrains', 'sports', 89.99, NULL, 60, 'running-shoes.jpg', 'Breathable Material\nCushioned Sole\nLightweight\nVarious Sizes'),
('Novel Book', 'Bestselling fiction novel', 'books', 14.99, 19.99, 80, 'book.jpg', 'Hardcover Edition\n500+ Pages\nBestseller\nGreat Story'),
('Face Cream', 'Anti-aging face cream with natural ingredients', 'beauty', 29.99, NULL, 40, 'face-cream.jpg', 'Anti-aging\nNatural Ingredients\nSPF Protection\n50ml Size'),
('Action Figure', 'Collectible action figure from popular series', 'toys', 19.99, 24.99, 35, 'action-figure.jpg', 'Collectible Item\nArticulated Joints\nIncludes Accessories\nGreat Gift'),
('Leather Watch', 'Classic leather strap watch', 'accessories', 129.99, NULL, 20, 'watch.jpg', 'Genuine Leather\nWater Resistant\nQuartz Movement\nElegant Design'),
('Laptop Bag', 'Durable laptop bag with multiple compartments', 'accessories', 39.99, 49.99, 45, 'laptop-bag.jpg', 'Padded Protection\nMultiple Pockets\nDurable Material\nFits 15" Laptops')
ON DUPLICATE KEY UPDATE name=name;

