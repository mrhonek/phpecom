<?php
// Direct database access script to update product images
// This script bypasses Laravel's routing system

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get database credentials from environment variables
function getDbCredentials() {
    // Try to get from environment variables (Railway sets these directly)
    $credentials = [
        'host' => getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? null,
        'port' => getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? null,
        'database' => getenv('DB_DATABASE') ?: $_ENV['DB_DATABASE'] ?? null,
        'username' => getenv('DB_USERNAME') ?: $_ENV['DB_USERNAME'] ?? null,
        'password' => getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? null,
    ];
    
    // Check if we got credentials
    if (!$credentials['host'] || !$credentials['database'] || !$credentials['username']) {
        // Fall back to common values
        $credentials = [
            'host' => 'containers-us-west-144.railway.app', // Common Railway MySQL host
            'port' => '6087', // Common Railway port
            'database' => 'railway',
            'username' => 'root',
            'password' => getenv('MYSQL_ROOT_PASSWORD') ?: $_ENV['MYSQL_ROOT_PASSWORD'] ?? null,
        ];
    }
    
    return $credentials;
}

// Connect to the database
function connectToDatabase() {
    $credentials = getDbCredentials();
    
    try {
        $dsn = "mysql:host={$credentials['host']}";
        if ($credentials['port']) {
            $dsn .= ";port={$credentials['port']}";
        }
        if ($credentials['database']) {
            $dsn .= ";dbname={$credentials['database']}";
        }
        
        $pdo = new PDO(
            $dsn,
            $credentials['username'],
            $credentials['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        return $pdo;
    } catch (PDOException $e) {
        return [
            'error' => 'Database connection failed: ' . $e->getMessage(),
            'credentials' => [
                'host' => $credentials['host'],
                'port' => $credentials['port'],
                'database' => $credentials['database'],
                'username' => $credentials['username'],
            ]
        ];
    }
}

// List all environment variables (for debugging)
function listEnvironmentVariables() {
    $envVars = [];
    foreach ($_ENV as $key => $value) {
        if (strpos(strtolower($key), 'password') === false && 
            strpos(strtolower($key), 'secret') === false) {
            $envVars[$key] = $value;
        } else {
            $envVars[$key] = '[REDACTED]';
        }
    }
    
    return $envVars;
}

// Update product images
function updateProductImages($pdo) {
    // Define product images data
    $products = [
        [
            'id' => 1,
            'image_filename' => 'smartphone-xs-pro.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Smartphone XS Pro on a wooden surface',
            'image_thumbnail' => 'smartphone-xs-pro-thumb.jpg',
        ],
        [
            'id' => 2,
            'image_filename' => 'ultra-hd-tv.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Ultra HD Smart TV in modern living room',
            'image_thumbnail' => 'ultra-hd-tv-thumb.jpg',
        ],
        [
            'id' => 3,
            'image_filename' => 'wireless-headphones.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Wireless noise-cancelling headphones in black',
            'image_thumbnail' => 'wireless-headphones-thumb.jpg',
        ],
        [
            'id' => 4,
            'image_filename' => 'digital-camera.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Professional digital camera with lens',
            'image_thumbnail' => 'digital-camera-thumb.jpg',
        ],
        [
            'id' => 5,
            'image_filename' => 'bluetooth-speaker.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Portable bluetooth speaker in blue color',
            'image_thumbnail' => 'bluetooth-speaker-thumb.jpg',
        ],
        [
            'id' => 6,
            'image_filename' => 'fitness-smartwatch.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Fitness smartwatch showing heart rate',
            'image_thumbnail' => 'fitness-smartwatch-thumb.jpg',
        ],
        [
            'id' => 7,
            'image_filename' => 'coffee-grinder.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Electric coffee grinder with coffee beans',
            'image_thumbnail' => 'coffee-grinder-thumb.jpg',
        ],
        [
            'id' => 8,
            'image_filename' => 'mechanical-keyboard.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Mechanical keyboard with RGB lighting',
            'image_thumbnail' => 'mechanical-keyboard-thumb.jpg',
        ],
        [
            'id' => 9,
            'image_filename' => 'office-chair.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Ergonomic office chair in black',
            'image_thumbnail' => 'office-chair-thumb.jpg',
        ],
        [
            'id' => 10,
            'image_filename' => 'smart-home-hub.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Smart home hub on a coffee table',
            'image_thumbnail' => 'smart-home-hub-thumb.jpg',
        ],
    ];
    
    $updatedCount = 0;
    $errors = [];
    
    try {
        foreach ($products as $product) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET image_filename = :image_filename,
                    image_path = :image_path,
                    image_alt = :image_alt,
                    image_thumbnail = :image_thumbnail
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                ':id' => $product['id'],
                ':image_filename' => $product['image_filename'],
                ':image_path' => $product['image_path'],
                ':image_alt' => $product['image_alt'],
                ':image_thumbnail' => $product['image_thumbnail']
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $updatedCount++;
            }
        }
        
        return [
            'status' => 'success',
            'message' => "Updated {$updatedCount} products with image data",
        ];
    } catch (PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to update products: ' . $e->getMessage()
        ];
    }
}

// Main execution
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($debug) {
    echo json_encode([
        'environment' => listEnvironmentVariables(),
        'credentials' => getDbCredentials()
    ]);
    exit;
}

$connection = connectToDatabase();

if (isset($connection['error'])) {
    echo json_encode($connection);
    exit;
}

$result = updateProductImages($connection);
echo json_encode($result); 