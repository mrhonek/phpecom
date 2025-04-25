<?php

/**
 * Database Connection
 * This file handles PostgreSQL database connections
 */

function getDbConnection() {
    // Debug information to see what environment variables are available
    $debug_info = [];
    
    // Hard-coded Railway values (based on user feedback)
    $railway_host = 'postgres.railway.internal';
    $railway_dbname = 'railway';
    
    // First check if Railway variables exist with expected values
    if (getenv('RAILWAY_ENVIRONMENT') !== false) {
        $debug_info['using_railway_defaults'] = true;
        $db_host = $railway_host;
        $db_port = getenv('PGPORT') ?: '5432';
        $db_name = $railway_dbname;
        $db_user = getenv('PGUSER') ?: 'postgres';
        $db_password = getenv('PGPASSWORD') ?: '';
    }
    // Get environment variables - Railway provides database URL as DATABASE_URL
    else if ($database_url = getenv('DATABASE_URL')) {
        $debug_info['using_database_url'] = true;
        // Parse the DATABASE_URL
        $db_params = parse_url($database_url);
        $db_host = $db_params['host'] ?? null;
        $db_port = $db_params['port'] ?? null;
        $db_name = ltrim($db_params['path'] ?? '', '/');
        $db_user = $db_params['user'] ?? null;
        $db_password = $db_params['pass'] ?? null;
    } else {
        // Specific Railway PostgreSQL environment variables
        $db_host = getenv('PGHOST') ?: getenv('DATABASE_HOST');
        $db_port = getenv('PGPORT') ?: getenv('DATABASE_PORT');
        $db_name = getenv('PGDATABASE') ?: getenv('DATABASE_NAME');
        $db_user = getenv('PGUSER') ?: getenv('DATABASE_USERNAME');
        $db_password = getenv('PGPASSWORD') ?: getenv('DATABASE_PASSWORD');
        
        $debug_info['using_specific_vars'] = true;
        $debug_info['host_var'] = [
            'PGHOST' => getenv('PGHOST'),
            'DATABASE_HOST' => getenv('DATABASE_HOST')
        ];
    }
    
    // Fall back to defaults if still not set
    $db_host = $db_host ?: 'localhost';
    $db_port = $db_port ?: '5432';
    $db_name = $db_name ?: 'phpecom';
    $db_user = $db_user ?: 'postgres';
    $db_password = $db_password ?: 'postgres';
    
    // Add all environment variables for debugging
    $env_vars = [];
    foreach ($_ENV as $key => $value) {
        if (strpos(strtolower($key), 'database') !== false || 
            strpos(strtolower($key), 'db_') !== false || 
            strpos(strtolower($key), 'pg') !== false || 
            strpos(strtolower($key), 'railway') !== false) {
            $env_vars[$key] = (strpos(strtolower($key), 'password') !== false) ? '******' : $value;
        }
    }
    $debug_info['relevant_env_vars'] = $env_vars;
    
    $debug_info['final_connection'] = [
        'host' => $db_host,
        'port' => $db_port,
        'dbname' => $db_name,
        'user' => $db_user,
        'password' => $db_password ? '******' : null
    ];
    
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
            'status' => 500,
            'debug' => $debug_info
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