<?php

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS for all domains
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database functions
require_once 'db.php';

// Determine the requested route
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = explode('/', $path);

// Simple API router
$response = [
    'error' => 'Route not found',
    'status' => 404
];

// Root endpoint - API info
if ($path === '' || $path === 'api') {
    $response = [
        'message' => 'PHPecom API running successfully',
        'status' => 'ok',
        'version' => '1.0.0',
        'endpoints' => [
            '/api/products',
            '/api/products/{id}',
            '/api/health',
            '/api/setup',
            '/api/users/register',
            '/api/users/login'
        ]
    ];
}

// Health check
if ($path === 'api/health') {
    // Try connecting to the database as part of health check
    $db = getDbConnection();
    
    if (is_array($db) && isset($db['error'])) {
        $response = [
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $db['error'],
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } else {
        $response = [
            'status' => 'ok',
            'message' => 'Service is healthy',
            'database' => 'connected',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Products endpoint - return data from database
if ($path === 'api/products') {
    $db = getDbConnection();
    
    if (is_array($db) && isset($db['error'])) {
        $response = $db;
    } else {
        try {
            $stmt = $db->query("SELECT * FROM products ORDER BY id");
            $products = $stmt->fetchAll();
            
            $response = [
                'status' => 'success',
                'products' => $products
            ];
        } catch (PDOException $e) {
            $response = [
                'error' => 'Error fetching products: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }
}

// Single product endpoint
if (count($path_parts) === 3 && $path_parts[0] === 'api' && $path_parts[1] === 'products' && is_numeric($path_parts[2])) {
    $product_id = (int)$path_parts[2];
    $db = getDbConnection();
    
    if (is_array($db) && isset($db['error'])) {
        $response = $db;
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                $response = [
                    'status' => 'success',
                    'product' => $product
                ];
            } else {
                $response = [
                    'error' => 'Product not found',
                    'status' => 404
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'error' => 'Error fetching product: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }
}

// Database setup endpoint
if ($path === 'api/setup') {
    $result = setupDatabase();
    
    if ($result === true) {
        $response = [
            'status' => 'success',
            'message' => 'Database setup completed successfully'
        ];
    } else {
        $response = $result;
    }
}

// User registration endpoint
if ($path === 'api/users/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['password'])) {
        $response = [
            'error' => 'Invalid input. Required fields: name, email, password',
            'status' => 400
        ];
    } else {
        $db = getDbConnection();
        
        if (is_array($db) && isset($db['error'])) {
            $response = $db;
        } else {
            try {
                // Check if email already exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$input['email']]);
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    $response = [
                        'error' => 'Email already in use',
                        'status' => 409
                    ];
                } else {
                    // Hash password
                    $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$input['name'], $input['email'], $password_hash]);
                    
                    $user_id = $db->lastInsertId();
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'User registered successfully',
                        'user_id' => $user_id
                    ];
                }
            } catch (PDOException $e) {
                $response = [
                    'error' => 'Error registering user: ' . $e->getMessage(),
                    'status' => 500
                ];
            }
        }
    }
}

// User login endpoint
if ($path === 'api/users/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        $response = [
            'error' => 'Invalid input. Required fields: email, password',
            'status' => 400
        ];
    } else {
        $db = getDbConnection();
        
        if (is_array($db) && isset($db['error'])) {
            $response = $db;
        } else {
            try {
                // Find user by email
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$input['email']]);
                $user = $stmt->fetch();
                
                if (!$user || !password_verify($input['password'], $user['password'])) {
                    $response = [
                        'error' => 'Invalid credentials',
                        'status' => 401
                    ];
                } else {
                    // Generate a simple token (in a real app, use JWT)
                    $token = bin2hex(random_bytes(32));
                    
                    $response = [
                        'status' => 'success',
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email']
                        ],
                        'token' => $token
                    ];
                }
            } catch (PDOException $e) {
                $response = [
                    'error' => 'Error during login: ' . $e->getMessage(),
                    'status' => 500
                ];
            }
        }
    }
}

// Get all environment variables (for debugging)
if ($path === 'api/env') {
    // Allow in all environments for debugging
    $env_vars = [];
    foreach ($_ENV as $key => $value) {
        // Mask sensitive data
        if (strpos(strtolower($key), 'password') !== false || 
            strpos(strtolower($key), 'secret') !== false || 
            strpos(strtolower($key), 'key') !== false) {
            $env_vars[$key] = '******';
        } else {
            $env_vars[$key] = $value;
        }
    }
    
    // Also check getenv() 
    $getenv_vars = [];
    $env_list = [
        'DATABASE_URL', 'PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD',
        'DATABASE_HOST', 'DATABASE_PORT', 'DATABASE_NAME', 'DATABASE_USERNAME', 'DATABASE_PASSWORD',
        'RAILWAY_ENVIRONMENT', 'PORT', 'RAILWAY_SERVICE_NAME'
    ];
    foreach ($env_list as $key) {
        $value = getenv($key);
        if ($value !== false) {
            if (strpos(strtolower($key), 'password') !== false || 
                strpos(strtolower($key), 'secret') !== false || 
                strpos(strtolower($key), 'key') !== false) {
                $getenv_vars[$key] = '******';
            } else {
                $getenv_vars[$key] = $value;
            }
        }
    }
    
    // Check for Railway-specific environment variables
    $railway_vars = [
        'RAILWAY_ENVIRONMENT' => getenv('RAILWAY_ENVIRONMENT'),
        'RAILWAY_SERVICE_NAME' => getenv('RAILWAY_SERVICE_NAME'),
        'DATABASE_URL' => getenv('DATABASE_URL') ? 'Available' : 'Not set'
    ];
    
    $response = [
        'env_vars' => $env_vars,
        'getenv_vars' => $getenv_vars,
        'railway_vars' => $railway_vars,
        'runtime_info' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'current_time' => date('Y-m-d H:i:s')
        ]
    ];
}

// Set HTTP status code if error
if (isset($response['status']) && is_numeric($response['status']) && $response['status'] >= 400) {
    http_response_code($response['status']);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT); 