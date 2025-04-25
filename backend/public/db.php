<?php

/**
 * Database Connection
 * This file handles PostgreSQL database connections
 */

function getDbConnection() {
    // Get environment variables - Railway automatically provides these
    $db_host = getenv('PGHOST') ?: 'localhost';
    $db_port = getenv('PGPORT') ?: '5432';
    $db_name = getenv('PGDATABASE') ?: 'phpecom';
    $db_user = getenv('PGUSER') ?: 'postgres';
    $db_password = getenv('PGPASSWORD') ?: 'postgres';
    
    try {
        $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
        $pdo = new PDO($dsn, $db_user, $db_password);
        
        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        return [
            'error' => 'Database connection failed: ' . $e->getMessage(),
            'status' => 500
        ];
    }
}

/**
 * Create the tables if they don't exist
 */
function setupDatabase() {
    $pdo = getDbConnection();
    
    if (is_array($pdo) && isset($pdo['error'])) {
        return $pdo;
    }
    
    try {
        // Create products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Check if products table is empty and seed with sample data
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Seed with sample products
            $pdo->exec("
                INSERT INTO products (name, price, description) VALUES
                ('Product 1', 19.99, 'Description for product 1'),
                ('Product 2', 29.99, 'Description for product 2'),
                ('Product 3', 39.99, 'Description for product 3')
            ");
        }
        
        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                total_amount DECIMAL(10, 2) NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Create order_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id SERIAL PRIMARY KEY,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");
        
        return true;
    } catch (PDOException $e) {
        return [
            'error' => 'Database setup failed: ' . $e->getMessage(),
            'status' => 500
        ];
    }
} 